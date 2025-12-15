<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletChargeLog extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'charge_logs';

    protected $guarded = ['id'];
}
