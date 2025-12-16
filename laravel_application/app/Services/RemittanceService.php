<?php

namespace App\Services;

use App\Models\Remittance;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for handling remittances (money transfers for expatriates)
 */
class RemittanceService
{
    protected $currencyService;
    protected $walletService;

    public function __construct(CurrencyService $currencyService, WalletService $walletService)
    {
        $this->currencyService = $currencyService;
        $this->walletService = $walletService;
    }

    /**
     * Create a new remittance
     *
     * @param array $data
     * @return Remittance
     */
    public function createRemittance($data)
    {
        DB::beginTransaction();

        try {
            $senderUser = User::findOrFail($data['sender_user_id']);

            // Validate sender is expatriate or has permission
            if (!$senderUser->is_expatriate) {
                throw new Exception('Only expatriates can send remittances');
            }

            // Calculate amounts and fees
            $calculation = $this->calculateRemittance(
                $data['send_amount'],
                $data['send_currency'],
                $data['delivery_method']
            );

            // Generate unique numbers
            $remittanceNumber = Remittance::generateRemittanceNumber();
            $pickupCode = null;

            if ($data['delivery_method'] === 'cash_pickup') {
                $pickupCode = Remittance::generatePickupCode();
            }

            // Create remittance
            $remittance = Remittance::create([
                'remittance_number' => $remittanceNumber,
                'sender_user_id' => $data['sender_user_id'],
                'sender_name' => $senderUser->name,
                'sender_country' => $senderUser->expatriate_location ?? $data['sender_country'],
                'sender_phone' => $senderUser->phone ?? $data['sender_phone'],
                'sender_email' => $senderUser->email,
                'receiver_user_id' => $data['receiver_user_id'] ?? null,
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'receiver_email' => $data['receiver_email'] ?? null,
                'receiver_national_id' => $data['receiver_national_id'] ?? null,
                'receiver_city' => $data['receiver_city'] ?? null,
                'receiver_address' => $data['receiver_address'] ?? null,
                'send_amount' => $calculation['send_amount'],
                'send_currency' => $data['send_currency'],
                'exchange_rate' => $calculation['exchange_rate'],
                'receive_amount' => $calculation['receive_amount'],
                'receive_currency' => 'SDG',
                'service_fee' => $calculation['service_fee'],
                'fee_currency' => $data['send_currency'],
                'total_charged' => $calculation['total_charged'],
                'delivery_method' => $data['delivery_method'],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'mobile_money_provider' => $data['mobile_money_provider'] ?? null,
                'mobile_money_number' => $data['mobile_money_number'] ?? null,
                'pickup_location' => $data['pickup_location'] ?? null,
                'pickup_code' => $pickupCode,
                'purpose' => $data['purpose'] ?? null,
                'status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'requires_verification' => $calculation['total_charged'] > 1000 // Require verification for amounts over $1000
            ]);

            // Charge sender's wallet
            $this->chargeSenderWallet($remittance, $senderUser);

            DB::commit();

            // Send notifications
            $this->sendRemittanceNotifications($remittance);

            return $remittance;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create remittance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate remittance amounts and fees
     *
     * @param float $sendAmount
     * @param string $sendCurrency
     * @param string $deliveryMethod
     * @return array
     */
    public function calculateRemittance($sendAmount, $sendCurrency, $deliveryMethod)
    {
        // Get exchange rate
        $exchangeRate = $this->currencyService->getExchangeRate($sendCurrency, 'SDG');

        if (!$exchangeRate) {
            throw new Exception('Unable to get exchange rate for ' . $sendCurrency);
        }

        // Calculate receive amount
        $receiveAmount = $sendAmount * $exchangeRate;

        // Calculate service fee based on delivery method
        $serviceFee = $this->calculateServiceFee($sendAmount, $sendCurrency, $deliveryMethod);

        // Total charged to sender
        $totalCharged = $sendAmount + $serviceFee;

        return [
            'send_amount' => $sendAmount,
            'send_currency' => $sendCurrency,
            'exchange_rate' => $exchangeRate,
            'receive_amount' => $receiveAmount,
            'receive_currency' => 'SDG',
            'service_fee' => $serviceFee,
            'fee_currency' => $sendCurrency,
            'total_charged' => $totalCharged
        ];
    }

    /**
     * Calculate service fee based on delivery method
     *
     * @param float $amount
     * @param string $currency
     * @param string $deliveryMethod
     * @return float
     */
    protected function calculateServiceFee($amount, $currency, $deliveryMethod)
    {
        // Fee structure based on delivery method
        $feeRates = [
            'wallet' => 0.01,        // 1% for wallet transfer
            'bank_transfer' => 0.015, // 1.5% for bank transfer
            'cash_pickup' => 0.02,    // 2% for cash pickup
            'mobile_money' => 0.015,  // 1.5% for mobile money
            'home_delivery' => 0.03   // 3% for home delivery
        ];

        $feeRate = $feeRates[$deliveryMethod] ?? 0.02;

        // Calculate percentage fee
        $fee = $amount * $feeRate;

        // Minimum fee (equivalent to $2 USD)
        $minFee = $this->currencyService->convert(2, 'USD', $currency);

        // Maximum fee (equivalent to $50 USD)
        $maxFee = $this->currencyService->convert(50, 'USD', $currency);

        return max($minFee, min($fee, $maxFee));
    }

    /**
     * Charge sender's wallet
     *
     * @param Remittance $remittance
     * @param User $sender
     * @return bool
     */
    protected function chargeSenderWallet(Remittance $remittance, User $sender)
    {
        // Convert total charged to SDG for wallet transaction
        $amountInSDG = $this->currencyService->convertToWalletCurrency(
            $remittance->total_charged,
            $remittance->send_currency
        );

        // Deduct from sender's wallet
        try {
            $transaction = $this->walletService->deductBalance(
                $sender->wallet_user_id,
                $amountInSDG,
                'Remittance ' . $remittance->remittance_number
            );

            if ($transaction) {
                $remittance->sender_transaction_id = $transaction->trx ?? null;
                $remittance->save();
                return true;
            }

            throw new Exception('Failed to charge sender wallet');

        } catch (Exception $e) {
            throw new Exception('Insufficient wallet balance or transaction failed');
        }
    }

    /**
     * Process remittance (approve and send to receiver)
     *
     * @param int $remittanceId
     * @return Remittance
     */
    public function processRemittance($remittanceId)
    {
        DB::beginTransaction();

        try {
            $remittance = Remittance::findOrFail($remittanceId);

            // Validate status
            if ($remittance->status !== 'pending') {
                throw new Exception('Remittance is not in pending status');
            }

            // Update status to processing
            $remittance->updateStatus('processing', 'Remittance is being processed');

            // Check if requires verification
            if ($remittance->requires_verification && !$remittance->compliance_checked) {
                throw new Exception('Remittance requires compliance verification');
            }

            // Approve remittance
            $remittance->updateStatus('approved', 'Remittance approved');

            // Process based on delivery method
            switch ($remittance->delivery_method) {
                case 'wallet':
                    $this->deliverToWallet($remittance);
                    break;

                case 'bank_transfer':
                    $this->initiateBankTransfer($remittance);
                    break;

                case 'cash_pickup':
                    $this->prepareForCashPickup($remittance);
                    break;

                case 'mobile_money':
                    $this->sendToMobileMoney($remittance);
                    break;

                case 'home_delivery':
                    $this->arrangeHomeDelivery($remittance);
                    break;
            }

            DB::commit();

            return $remittance;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process remittance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deliver funds directly to receiver's wallet
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function deliverToWallet(Remittance $remittance)
    {
        if (!$remittance->receiver_user_id) {
            throw new Exception('Receiver must have a user account for wallet delivery');
        }

        $receiver = User::find($remittance->receiver_user_id);

        if (!$receiver || !$receiver->wallet_user_id) {
            throw new Exception('Receiver does not have a wallet');
        }

        // Add funds to receiver's wallet
        $transaction = $this->walletService->addBalance(
            $receiver->wallet_user_id,
            $remittance->receive_amount,
            'Remittance received from ' . $remittance->sender_name
        );

        if ($transaction) {
            $remittance->receiver_transaction_id = $transaction->trx ?? null;
            $remittance->updateStatus('completed', 'Funds delivered to wallet');
            return true;
        }

        throw new Exception('Failed to deliver funds to wallet');
    }

    /**
     * Initiate bank transfer
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function initiateBankTransfer(Remittance $remittance)
    {
        // In production, integrate with bank API
        // For now, mark as in_transit
        $remittance->updateStatus('in_transit', 'Bank transfer initiated to ' . $remittance->bank_name);

        // Would normally integrate with bank API here
        Log::info('Bank transfer initiated for remittance: ' . $remittance->remittance_number);

        return true;
    }

    /**
     * Prepare for cash pickup
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function prepareForCashPickup(Remittance $remittance)
    {
        $remittance->updateStatus('ready_for_pickup', 'Cash is ready for pickup at ' . $remittance->pickup_location);

        // Send pickup code to receiver
        Log::info('Cash pickup ready. Code: ' . $remittance->pickup_code);

        return true;
    }

    /**
     * Send to mobile money
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function sendToMobileMoney(Remittance $remittance)
    {
        // In production, integrate with mobile money API (Zain Cash, MTN, etc.)
        $remittance->updateStatus('in_transit', 'Mobile money transfer initiated');

        Log::info('Mobile money transfer to ' . $remittance->mobile_money_number);

        return true;
    }

    /**
     * Arrange home delivery
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function arrangeHomeDelivery(Remittance $remittance)
    {
        // In production, integrate with delivery service
        $remittance->updateStatus('in_transit', 'Home delivery arranged');

        Log::info('Home delivery to ' . $remittance->receiver_address);

        return true;
    }

    /**
     * Cancel remittance and refund sender
     *
     * @param int $remittanceId
     * @param string $reason
     * @return Remittance
     */
    public function cancelRemittance($remittanceId, $reason = null)
    {
        DB::beginTransaction();

        try {
            $remittance = Remittance::findOrFail($remittanceId);

            if (!$remittance->canBeCancelled()) {
                throw new Exception('Remittance cannot be cancelled');
            }

            // Refund to sender's wallet
            $this->refundSenderWallet($remittance);

            // Update status
            $remittance->updateStatus('cancelled', $reason ?? 'Cancelled by user');

            DB::commit();

            return $remittance;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Refund sender's wallet
     *
     * @param Remittance $remittance
     * @return bool
     */
    protected function refundSenderWallet(Remittance $remittance)
    {
        $sender = $remittance->sender;

        if (!$sender || !$sender->wallet_user_id) {
            throw new Exception('Cannot refund: Sender wallet not found');
        }

        // Convert total charged back to SDG
        $refundAmountInSDG = $this->currencyService->convertToWalletCurrency(
            $remittance->total_charged,
            $remittance->send_currency
        );

        // Add refund to sender's wallet
        $transaction = $this->walletService->addBalance(
            $sender->wallet_user_id,
            $refundAmountInSDG,
            'Remittance refund: ' . $remittance->remittance_number
        );

        if ($transaction) {
            $remittance->updateStatus('refunded', 'Funds refunded to sender');
            return true;
        }

        throw new Exception('Failed to refund wallet');
    }

    /**
     * Get remittance statistics for user
     *
     * @param int $userId
     * @param string $type ('sent' or 'received')
     * @return array
     */
    public function getUserRemittanceStats($userId, $type = 'sent')
    {
        $column = $type === 'sent' ? 'sender_user_id' : 'receiver_user_id';

        $stats = [
            'total_count' => Remittance::where($column, $userId)->count(),
            'total_amount' => 0,
            'pending_count' => Remittance::where($column, $userId)->where('status', 'pending')->count(),
            'completed_count' => Remittance::where($column, $userId)->where('status', 'completed')->count(),
            'this_month_count' => Remittance::where($column, $userId)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return $stats;
    }

    /**
     * Send remittance notifications
     *
     * @param Remittance $remittance
     */
    protected function sendRemittanceNotifications(Remittance $remittance)
    {
        // In production, send SMS/Email notifications
        Log::info('Remittance created: ' . $remittance->remittance_number);

        // Notify sender
        // Notify receiver
    }
}
