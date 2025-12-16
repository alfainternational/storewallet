<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use App\Models\WalletCurrency;
use App\Models\WalletMerchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Escrow Service - Manages secure payment holding and release
 *
 * Flow:
 * 1. Order Created -> Hold funds in escrow
 * 2. Order Delivered & Confirmed -> Release funds to merchant
 * 3. Order Cancelled -> Refund to customer
 */
class EscrowService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Hold funds when order is created
     *
     * @param Order $order
     * @return array ['success' => bool, 'escrow_trx' => string, 'message' => string]
     */
    public function holdFunds(Order $order)
    {
        $connection = DB::connection('wallet_db');
        $connection->beginTransaction();

        try {
            $user = $order->user;

            if (!$user->wallet_user_id) {
                throw new Exception("User wallet not found");
            }

            // Calculate total amount
            $totalAmount = $this->calculateOrderTotal($order);

            // Get currency
            $currency = WalletCurrency::where('currency_code', 'USD')->first();
            if (!$currency) {
                throw new Exception("Currency not found");
            }

            // Get user wallet balance
            $userWallet = WalletBalance::where('user_id', $user->wallet_user_id)
                ->where('user_type', 'USER')
                ->where('currency_id', $currency->id)
                ->lockForUpdate()
                ->first();

            if (!$userWallet) {
                // Auto-create wallet balance
                $userWallet = new WalletBalance();
                $userWallet->user_id = $user->wallet_user_id;
                $userWallet->user_type = 'USER';
                $userWallet->currency_id = $currency->id;
                $userWallet->balance = 0;
                $userWallet->save();
            }

            if ($userWallet->balance < $totalAmount) {
                throw new Exception("Insufficient wallet balance. Required: $totalAmount, Available: {$userWallet->balance}");
            }

            // Deduct from user wallet
            $userWallet->balance -= $totalAmount;
            $userWallet->save();

            // Create escrow transaction (held funds)
            $trx = $this->generateTrx();

            $escrowTrx = new WalletTransaction();
            $escrowTrx->user_id = $user->wallet_user_id;
            $escrowTrx->user_type = 'USER';
            $escrowTrx->wallet_id = $userWallet->id;
            $escrowTrx->currency_id = $currency->id;
            $escrowTrx->before_charge = $totalAmount;
            $escrowTrx->amount = $totalAmount;
            $escrowTrx->post_balance = $userWallet->balance;
            $escrowTrx->charge = 0;
            $escrowTrx->charge_type = '+';
            $escrowTrx->trx_type = '-';
            $escrowTrx->remark = 'escrow_hold';
            $escrowTrx->details = "Funds held in escrow for Order #{$order->id}";
            $escrowTrx->receiver_id = null; // In escrow, no receiver yet
            $escrowTrx->receiver_type = 'ESCROW';
            $escrowTrx->trx = $trx;
            $escrowTrx->created_at = now();
            $escrowTrx->updated_at = now();
            $escrowTrx->save();

            // Update order payment with escrow transaction
            $order->payment->update([
                'escrow_trx' => $trx,
                'escrow_status' => 'held',
                'escrow_amount' => $totalAmount
            ]);

            $connection->commit();

            return [
                'success' => true,
                'escrow_trx' => $trx,
                'message' => 'Funds successfully held in escrow'
            ];

        } catch (Exception $e) {
            $connection->rollBack();
            Log::error("Escrow hold failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Release funds to merchant when order is delivered
     *
     * @param Order $order
     * @return array
     */
    public function releaseFunds(Order $order)
    {
        $connection = DB::connection('wallet_db');
        $connection->beginTransaction();

        try {
            $payment = $order->payment;

            if ($payment->escrow_status !== 'held') {
                throw new Exception("Funds not in escrow");
            }

            $totalAmount = $payment->escrow_amount;
            $currency = WalletCurrency::where('currency_code', 'USD')->first();

            // Get merchant for the market
            $market = $order->productOrders->first()->product->market;

            // Get or create merchant wallet account
            $merchantWallet = $this->getOrCreateMerchantWallet($market, $currency->id);

            // Calculate platform fee (5%)
            $platformFee = $totalAmount * 0.05;
            $merchantAmount = $totalAmount - $platformFee;

            // Add to merchant wallet
            $merchantWallet->balance += $merchantAmount;
            $merchantWallet->save();

            // Create merchant transaction
            $trx = $this->generateTrx();

            $merchTrx = new WalletTransaction();
            $merchTrx->user_id = $merchantWallet->user_id;
            $merchTrx->user_type = 'MERCHANT';
            $merchTrx->wallet_id = $merchantWallet->id;
            $merchTrx->currency_id = $currency->id;
            $merchTrx->before_charge = $merchantAmount;
            $merchTrx->amount = $merchantAmount;
            $merchTrx->post_balance = $merchantWallet->balance;
            $merchTrx->charge = $platformFee;
            $merchTrx->charge_type = '-';
            $merchTrx->trx_type = '+';
            $merchTrx->remark = 'escrow_release';
            $merchTrx->details = "Payment received from Order #{$order->id}";
            $merchTrx->receiver_id = $order->user->wallet_user_id;
            $merchTrx->receiver_type = 'USER';
            $merchTrx->trx = $trx;
            $merchTrx->created_at = now();
            $merchTrx->updated_at = now();
            $merchTrx->save();

            // Update escrow status
            $payment->update([
                'escrow_status' => 'released',
                'release_trx' => $trx,
                'released_at' => now()
            ]);

            $connection->commit();

            return [
                'success' => true,
                'release_trx' => $trx,
                'merchant_amount' => $merchantAmount,
                'platform_fee' => $platformFee,
                'message' => 'Funds successfully released to merchant'
            ];

        } catch (Exception $e) {
            $connection->rollBack();
            Log::error("Escrow release failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Refund funds to customer when order is cancelled
     *
     * @param Order $order
     * @return array
     */
    public function refundFunds(Order $order)
    {
        $connection = DB::connection('wallet_db');
        $connection->beginTransaction();

        try {
            $payment = $order->payment;
            $user = $order->user;

            if ($payment->escrow_status !== 'held') {
                throw new Exception("Funds not in escrow");
            }

            $totalAmount = $payment->escrow_amount;
            $currency = WalletCurrency::where('currency_code', 'USD')->first();

            // Get user wallet
            $userWallet = WalletBalance::where('user_id', $user->wallet_user_id)
                ->where('user_type', 'USER')
                ->where('currency_id', $currency->id)
                ->lockForUpdate()
                ->first();

            if (!$userWallet) {
                throw new Exception("User wallet not found");
            }

            // Refund to user
            $userWallet->balance += $totalAmount;
            $userWallet->save();

            // Create refund transaction
            $trx = $this->generateTrx();

            $refundTrx = new WalletTransaction();
            $refundTrx->user_id = $user->wallet_user_id;
            $refundTrx->user_type = 'USER';
            $refundTrx->wallet_id = $userWallet->id;
            $refundTrx->currency_id = $currency->id;
            $refundTrx->before_charge = $totalAmount;
            $refundTrx->amount = $totalAmount;
            $refundTrx->post_balance = $userWallet->balance;
            $refundTrx->charge = 0;
            $refundTrx->charge_type = '+';
            $refundTrx->trx_type = '+';
            $refundTrx->remark = 'escrow_refund';
            $refundTrx->details = "Refund for cancelled Order #{$order->id}";
            $refundTrx->receiver_id = null;
            $refundTrx->receiver_type = 'REFUND';
            $refundTrx->trx = $trx;
            $refundTrx->created_at = now();
            $refundTrx->updated_at = now();
            $refundTrx->save();

            // Update escrow status
            $payment->update([
                'escrow_status' => 'refunded',
                'refund_trx' => $trx,
                'refunded_at' => now()
            ]);

            $connection->commit();

            return [
                'success' => true,
                'refund_trx' => $trx,
                'message' => 'Funds successfully refunded'
            ];

        } catch (Exception $e) {
            $connection->rollBack();
            Log::error("Escrow refund failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate order total including tax and delivery
     */
    private function calculateOrderTotal(Order $order)
    {
        $subtotal = 0;

        foreach ($order->productOrders as $productOrder) {
            $subtotal += $productOrder->price * $productOrder->quantity;
        }

        $total = $subtotal + $order->tax + $order->delivery_fee;

        return $total;
    }

    /**
     * Get or create merchant wallet
     */
    private function getOrCreateMerchantWallet($market, $currencyId)
    {
        // First, get or create merchant account
        $merchant = WalletMerchant::where('email', $market->users->first()->email)->first();

        if (!$merchant) {
            $merchant = new WalletMerchant();
            $merchant->firstname = $market->name;
            $merchant->lastname = 'Market';
            $merchant->username = 'merchant_' . $market->id;
            $merchant->email = $market->users->first()->email;
            $merchant->password = bcrypt(str_random(16));
            $merchant->status = 1;
            $merchant->save();
        }

        // Get or create wallet balance
        $wallet = WalletBalance::where('user_id', $merchant->id)
            ->where('user_type', 'MERCHANT')
            ->where('currency_id', $currencyId)
            ->first();

        if (!$wallet) {
            $wallet = new WalletBalance();
            $wallet->user_id = $merchant->id;
            $wallet->user_type = 'MERCHANT';
            $wallet->currency_id = $currencyId;
            $wallet->balance = 0;
            $wallet->save();
        }

        return $wallet;
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTrx($length = 12)
    {
        $characters = 'ABCDEFGHjkmnpqrstuvwxyz23456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
