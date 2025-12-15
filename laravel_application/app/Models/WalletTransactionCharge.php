<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransactionCharge extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'transaction_charges';

    protected $guarded = ['id'];
}
