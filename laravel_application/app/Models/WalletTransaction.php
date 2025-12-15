<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'transactions';

    protected $guarded = ['id'];

    // Override save to creating logs is handled in Service to avoid dependency issues
}
