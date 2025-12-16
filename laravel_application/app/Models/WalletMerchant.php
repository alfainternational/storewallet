<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletMerchant extends Model
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
    protected $table = 'merchants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'password',
        'country_code',
        'mobile',
        'company_name',
        'business_type',
        'status',
        'kv',
        'ev',
        'sv',
        'ts',
        'tv'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get full name
     */
    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get merchant wallets
     */
    public function wallets()
    {
        return $this->hasMany(WalletBalance::class, 'user_id', 'id')
            ->where('user_type', 'MERCHANT');
    }

    /**
     * Get merchant transactions
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'id')
            ->where('user_type', 'MERCHANT');
    }
}
