<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletCurrency extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'currencies';

    protected $guarded = ['id'];
}
