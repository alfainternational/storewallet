<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_name_ar',
        'shop_description',
        'shop_description_ar',
        'shop_logo',
        'shop_banner',
        'business_license',
        'tax_id',
        'bank_name',
        'bank_account',
        'commission_rate',
        'total_sales',
        'total_revenue',
        'rating',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_sales' => 'integer',
        'total_revenue' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }
}
