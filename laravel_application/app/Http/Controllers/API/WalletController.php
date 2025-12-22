<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\XCashService;
use App\Services\BankakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $user = $request->user();
        $balance = $user->wallet_balance ?? 0;
        $escrowBalance = $user->escrowTransactions()->where('status', 'holding')->sum('amount');
        $availableBalance = $balance - $escrowBalance;

        return response()->json([
            'success' => true,
            'balance' => $balance,
            'escrow_balance' => $escrowBalance,
            'available_balance' => $availableBalance,
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = WalletTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions->items(),
            'total' => $transactions->total(),
        ]);
    }

    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10',
            'method' => 'required|in:xcash,bankak,e15,sudanipay,stripe',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();

            // Process payment through gateway
            $paymentResult = $this->processPayment($request->method, $request->amount, $user);

            if (!$paymentResult['success']) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $paymentResult['message']], 422);
            }

            // Update wallet balance
            $user->increment('wallet_balance', $request->amount);

            // Create transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'status' => 'completed',
                'payment_method' => $request->method,
                'transaction_id' => $paymentResult['transaction_id'],
                'description' => 'Wallet deposit via ' . $request->method,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'balance' => $user->wallet_balance]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Deposit failed'], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10',
            'account' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $availableBalance = $user->wallet_balance - $user->escrowTransactions()->where('status', 'holding')->sum('amount');

            if ($availableBalance < $request->amount) {
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 422);
            }

            $user->decrement('wallet_balance', $request->amount);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdraw',
                'amount' => $request->amount,
                'status' => 'pending',
                'description' => 'Withdrawal to ' . $request->account,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'balance' => $user->wallet_balance]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Withdrawal failed'], 500);
        }
    }

    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $sender = $request->user();
            $recipient = \App\Models\User::where('phone', $request->phone)->first();

            if (!$recipient) {
                return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
            }

            if ($sender->id === $recipient->id) {
                return response()->json(['success' => false, 'message' => 'Cannot transfer to yourself'], 422);
            }

            $availableBalance = $sender->wallet_balance - $sender->escrowTransactions()->where('status', 'holding')->sum('amount');

            if ($availableBalance < $request->amount) {
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 422);
            }

            $sender->decrement('wallet_balance', $request->amount);
            $recipient->increment('wallet_balance', $request->amount);

            WalletTransaction::create([
                'user_id' => $sender->id,
                'type' => 'transfer_out',
                'amount' => $request->amount,
                'status' => 'completed',
                'description' => 'Transfer to ' . $recipient->first_name . ' ' . $recipient->last_name,
            ]);

            WalletTransaction::create([
                'user_id' => $recipient->id,
                'type' => 'transfer_in',
                'amount' => $request->amount,
                'status' => 'completed',
                'description' => 'Transfer from ' . $sender->first_name . ' ' . $sender->last_name,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'balance' => $sender->wallet_balance]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transfer failed'], 500);
        }
    }

    private function processPayment($method, $amount, $user)
    {
        // Simulate payment processing
        // In production, integrate with actual payment gateways
        return [
            'success' => true,
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
        ];
    }
}
