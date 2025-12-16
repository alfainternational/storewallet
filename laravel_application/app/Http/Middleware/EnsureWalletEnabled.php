<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWalletEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user has wallet account
        if (auth()->check() && !auth()->user()->wallet_user_id) {
            // Auto-create wallet for existing users
            $walletService = app(\App\Services\WalletService::class);

            try {
                $walletUser = $walletService->createWalletUser([
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'password' => bcrypt(str_random(16)) // Random password
                ]);

                if ($walletUser) {
                    auth()->user()->update(['wallet_user_id' => $walletUser->id]);
                }
            } catch (\Exception $e) {
                \Log::error('Wallet auto-creation failed: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}
