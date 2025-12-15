<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletUser extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'wallet_db';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'country_code', 'mobile', 'balance', 'ver_code', 'ver_code_send_at',
        'kv', 'ev', 'sv', 'ts', 'tv', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
