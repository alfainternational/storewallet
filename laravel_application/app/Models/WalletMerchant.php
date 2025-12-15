<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletMerchant extends Model
{
    protected $connection = 'wallet_db';
    protected $table = 'merchants';

    protected $guarded = ['id'];
}
