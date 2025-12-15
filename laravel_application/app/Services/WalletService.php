<?php

namespace App\Services;

use App\Models\WalletUser;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use App\Models\WalletMerchant;
use App\Models\WalletCurrency;
use App\Models\WalletTransactionCharge;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * Create a new user in the wallet database.
     *
     * @param array $data
     * @return WalletUser
     */
    public function createWalletUser(array $data)
    {
        try {
            $nameParts = explode(' ', $data['name'], 2);
            $firstname = $nameParts[0];
            $lastname = isset($nameParts[1]) ? $nameParts[1] : '';

            $walletUser = new WalletUser();
            $walletUser->firstname = $firstname;
            $walletUser->lastname = $lastname;
            $walletUser->email = strtolower($data['email']);
            $walletUser->password = Hash::make($data['password']); 
            
            $walletUser->kv = 1;
            $walletUser->ev = 1;
            $walletUser->sv = 1;
            $walletUser->ts = 0;
            $walletUser->tv = 1;
            $walletUser->status = 1;

            $walletUser->save();

            return $walletUser;
        } catch (Exception $e) {
            Log::error("Failed to create wallet user: " . $e->getMessage());
            return null;
        }
    }

    public function getWalletUser($email)
    {
        return WalletUser::where('email', $email)->first();
    }

    /**
     * Process payment from user wallet to store merchant wallet.
     *
     * @param int $walletUserId
     * @param float $amount
     * @param string $currencyCode
     * @return array ['success' => bool, 'message' => string, 'trx' => string]
     */
    public function processPayment($walletUserId, $amount, $currencyCode = 'USD')
    {
        $connection = DB::connection('wallet_db');
        $connection->beginTransaction();

        try {
            // 1. Get User Wallet
            $currency = WalletCurrency::where('currency_code', $currencyCode)->first();
            if (!$currency) {
                throw new Exception("Currency not supported");
            }

            $userWallet = WalletBalance::where('user_id', $walletUserId)
                ->where('user_type', 'USER')
                ->where('currency_id', $currency->id)
                ->lockForUpdate()
                ->first();

            if (!$userWallet) {
                 // Try to create wallet if not exists (auto-provision)
                 $userWallet = new WalletBalance();
                 $userWallet->user_id = $walletUserId;
                 $userWallet->user_type = 'USER';
                 $userWallet->currency_id = $currency->id;
                 $userWallet->balance = 0;
                 $userWallet->save();
            }

            if ($userWallet->balance < $amount) {
                throw new Exception("Insufficient balance");
            }

            // 2. Get Merchant Wallet
            $merchantUsername = env('WALLET_MERCHANT_USERNAME', 'store_merchant'); 
            $merchant = WalletMerchant::where('username', $merchantUsername)->first();
            if (!$merchant) {
                // Fallback or throw
                // For "Unified" simulation, let's create one if not exists or assume ID 1
                 $merchant = WalletMerchant::find(1);
                 if (!$merchant) throw new Exception("Store Merchant account not found");
            }

            $merchantWallet = WalletBalance::where('user_id', $merchant->id)
                ->where('user_type', 'MERCHANT')
                ->where('currency_id', $currency->id)
                ->lockForUpdate()
                ->first();

            if (!$merchantWallet) {
                 $merchantWallet = new WalletBalance();
                 $merchantWallet->user_id = $merchant->id;
                 $merchantWallet->user_type = 'MERCHANT';
                 $merchantWallet->currency_id = $currency->id;
                 $merchantWallet->balance = 0;
                 $merchantWallet->save();
            }

            // 3. Deduction
            $userWallet->balance -= $amount;
            $userWallet->save();

            $merchantWallet->balance += $amount;
            $merchantWallet->save();

            // 4. Create Transactions
            $trx = $this->generateTrx();
            
            // User Trx
            $senderTrx = new WalletTransaction();
            $senderTrx->user_id = $walletUserId;
            $senderTrx->user_type = 'USER';
            $senderTrx->wallet_id = $userWallet->id;
            $senderTrx->currency_id = $currency->id;
            $senderTrx->before_charge = $amount;
            $senderTrx->amount = $amount;
            $senderTrx->post_balance = $userWallet->balance;
            $senderTrx->charge = 0; // Assume 0 fee for internal store payments
            $senderTrx->charge_type = '+';
            $senderTrx->trx_type = '-';
            $senderTrx->remark = 'payment_store';
            $senderTrx->details = 'Payment to ' . $merchant->fullname;
            $senderTrx->receiver_id = $merchant->id;
            $senderTrx->receiver_type = 'MERCHANT';
            $senderTrx->trx = $trx;
            $senderTrx->created_at = now();
            $senderTrx->updated_at = now();
            $senderTrx->save();

            // Merchant Trx
            $merchTrx = new WalletTransaction();
            $merchTrx->user_id = $merchant->id;
            $merchTrx->user_type = 'MERCHANT';
            $merchTrx->wallet_id = $merchantWallet->id;
            $merchTrx->currency_id = $currency->id;
            $merchTrx->before_charge = $amount;
            $merchTrx->amount = $amount;
            $merchTrx->post_balance = $merchantWallet->balance;
            $merchTrx->charge = 0;
            $merchTrx->charge_type = '+';
            $merchTrx->trx_type = '+';
            $merchTrx->remark = 'payment_received';
            $merchTrx->details = 'Payment from User'; // Could fetch user name
            $merchTrx->receiver_id = $walletUserId;
            $merchTrx->receiver_type = 'USER';
            $merchTrx->trx = $trx;
            $merchTrx->created_at = now();
            $merchTrx->updated_at = now();
            $merchTrx->save();

            $connection->commit();

            return ['success' => true, 'trx' => $trx];

        } catch (Exception $e) {
            $connection->rollBack();
            Log::error("Payment Process Failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

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
