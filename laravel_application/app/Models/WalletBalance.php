<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletBalance extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'wallets';

    protected $guarded = ['id'];

    public function currency()
    {
        // We might need a WalletCurrency model if we want to access currency details
        // For now, assume we just use IDs
        return $this->belongsTo(WalletCurrency::class, 'currency_id');
    }
}
