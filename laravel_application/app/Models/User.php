<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role', // buyer, merchant, shipping_company, admin
        'city_id',
        'address',
        'avatar',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
        'is_expatriate',
        'country_of_residence',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_expatriate' => 'boolean',
    ];

    // Relationships
    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function shippingCompany()
    {
        return $this->hasOne(ShippingCompany::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sentRemittances()
    {
        return $this->hasMany(Remittance::class, 'sender_id');
    }

    public function receivedRemittances()
    {
        return $this->hasMany(Remittance::class, 'recipient_id');
    }

    public function city()
    {
        return $this->belongsTo(SudaneseCity::class, 'city_id');
    }

    public function verification()
    {
        return $this->hasOne(UserVerification::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFormattedPhoneAttribute()
    {
        return formatSudanesePhone($this->phone);
    }

    // Methods
    public function isMerchant()
    {
        return $this->role === 'merchant';
    }

    public function isShippingCompany()
    {
        return $this->role === 'shipping_company';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasVerifiedPhone()
    {
        return !is_null($this->phone_verified_at);
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }
}
