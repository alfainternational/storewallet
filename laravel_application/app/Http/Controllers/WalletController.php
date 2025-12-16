<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Models\WalletUser;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth');
        $this->walletService = $walletService;
    }

    /**
     * Display wallet dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Auto-create wallet if not exists
        if (!$user->wallet_user_id) {
            $walletUser = $this->walletService->createWalletUser([
                'name' => $user->name,
                'email' => $user->email,
                'password' => bcrypt(str_random(16))
            ]);

            if ($walletUser) {
                $user->update(['wallet_user_id' => $walletUser->id]);
            }
        }

        $walletUser = $this->walletService->getWalletUser($user->email);

        // Get wallet balances
        $balances = WalletBalance::where('user_id', $walletUser->id)
            ->where('user_type', 'USER')
            ->with('currency')
            ->get();

        // Get recent transactions
        $transactions = WalletTransaction::where('user_id', $walletUser->id)
            ->where('user_type', 'USER')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('wallet.index', compact('walletUser', 'balances', 'transactions'));
    }

    /**
     * Show deposit form
     */
    public function deposit()
    {
        $user = auth()->user();
        $walletUser = $this->walletService->getWalletUser($user->email);

        return view('wallet.deposit', compact('walletUser'));
    }

    /**
     * Process deposit
     */
    public function processDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string',
            'payment_method' => 'required|string'
        ]);

        // Here integrate with xcash deposit system
        // For now, redirect to xcash wallet deposit page
        $walletUrl = env('WALLET_URL', 'http://localhost/wallet');

        return redirect($walletUrl . '/user/deposit');
    }

    /**
     * Show withdrawal form
     */
    public function withdraw()
    {
        $user = auth()->user();
        $walletUser = $this->walletService->getWalletUser($user->email);

        $balances = WalletBalance::where('user_id', $walletUser->id)
            ->where('user_type', 'USER')
            ->with('currency')
            ->get();

        return view('wallet.withdraw', compact('walletUser', 'balances'));
    }

    /**
     * Process withdrawal
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency_id' => 'required|integer',
            'method' => 'required|string'
        ]);

        // Integrate with xcash withdrawal system
        $walletUrl = env('WALLET_URL', 'http://localhost/wallet');

        return redirect($walletUrl . '/user/withdraw');
    }

    /**
     * Show transaction history
     */
    public function transactions()
    {
        $user = auth()->user();
        $walletUser = $this->walletService->getWalletUser($user->email);

        $transactions = WalletTransaction::where('user_id', $walletUser->id)
            ->where('user_type', 'USER')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet.transactions', compact('transactions'));
    }

    /**
     * API: Get wallet balance
     */
    public function apiBalance()
    {
        $user = auth()->user();
        $walletUser = $this->walletService->getWalletUser($user->email);

        if (!$walletUser) {
            return response()->json(['error' => 'Wallet not found'], 404);
        }

        $balances = WalletBalance::where('user_id', $walletUser->id)
            ->where('user_type', 'USER')
            ->with('currency')
            ->get();

        return response()->json([
            'success' => true,
            'balances' => $balances
        ]);
    }
}
