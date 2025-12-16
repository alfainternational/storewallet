<?php

/*
|--------------------------------------------------------------------------
| Wallet Routes
|--------------------------------------------------------------------------
|
| Routes for wallet integration
|
*/

Route::group(['middleware' => ['auth', 'ensure.wallet.enabled']], function () {

    // Wallet Dashboard
    Route::get('/wallet', 'WalletController@index')->name('wallet.index');

    // Deposit
    Route::get('/wallet/deposit', 'WalletController@deposit')->name('wallet.deposit');
    Route::post('/wallet/deposit', 'WalletController@processDeposit')->name('wallet.deposit.process');

    // Withdraw
    Route::get('/wallet/withdraw', 'WalletController@withdraw')->name('wallet.withdraw');
    Route::post('/wallet/withdraw', 'WalletController@processWithdraw')->name('wallet.withdraw.process');

    // Transactions
    Route::get('/wallet/transactions', 'WalletController@transactions')->name('wallet.transactions');

    // API Routes
    Route::get('/api/wallet/balance', 'WalletController@apiBalance')->name('api.wallet.balance');
});
