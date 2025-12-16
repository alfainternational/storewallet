<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\WalletService;

class CheckWalletBalance
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle an incoming request.
     * Check if user has sufficient wallet balance before checkout
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && $request->route()->named('orders.create')) {
            $user = auth()->user();

            if (!$user->wallet_user_id) {
                return redirect()->back()->with('error', __('lang.wallet_account_required'));
            }

            // Get cart total
            $cartTotal = 0;
            $carts = \App\Models\Cart::where('user_id', $user->id)->get();

            foreach ($carts as $cart) {
                $cartTotal += $cart->product->price * $cart->quantity;

                // Add options price
                foreach ($cart->options as $option) {
                    $cartTotal += $option->price;
                }
            }

            // Get wallet balance
            $walletUser = $this->walletService->getWalletUser($user->email);
            if ($walletUser && $walletUser->balance < $cartTotal) {
                return redirect()->route('wallet.deposit')
                    ->with('error', __('lang.insufficient_wallet_balance'));
            }
        }

        return $next($request);
    }
}
