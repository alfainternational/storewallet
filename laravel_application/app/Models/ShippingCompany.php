<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShippingCompany
 * @package App\Models
 *
 * @property integer id
 * @property string name
 * @property string email
 * @property string phone
 * @property boolean active
 * @property boolean verified
 */
class ShippingCompany extends Model
{
    use SoftDeletes;

    public $table = 'shipping_companies';

    public $fillable = [
        'name',
        'business_name',
        'registration_number',
        'tax_number',
        'description',
        'logo',
        'email',
        'phone',
        'phone_secondary',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'manager_user_id',
        'service_type',
        'service_cities',
        'service_countries',
        'pricing_model',
        'base_price',
        'price_per_km',
        'price_per_kg',
        'wallet_merchant_id',
        'verified',
        'active',
        'rating',
        'total_shipments',
        'completed_shipments',
        'total_earnings',
        'pending_balance',
        'license_document',
        'insurance_document',
        'license_expiry'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'active' => 'boolean',
        'rating' => 'decimal:2',
        'base_price' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'service_cities' => 'array',
        'service_countries' => 'array',
        'license_expiry' => 'date'
    ];

    protected $dates = ['deleted_at', 'license_expiry'];

    /**
     * Validation rules
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:shipping_companies,email',
        'phone' => 'required|string',
        'address' => 'required',
        'city' => 'required',
        'manager_user_id' => 'required|exists:users,id'
    ];

    /**
     * Get the manager user
     */
    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_user_id');
    }

    /**
     * Get company drivers
     */
    public function companyDrivers()
    {
        return $this->hasMany(\App\Models\CompanyDriver::class, 'company_id');
    }

    /**
     * Get active drivers
     */
    public function activeDrivers()
    {
        return $this->companyDrivers()->where('active', true)->with('driver');
    }

    /**
     * Get all drivers through pivot
     */
    public function drivers()
    {
        return $this->belongsToMany(\App\Models\Driver::class, 'company_drivers', 'company_id', 'driver_id')
            ->withPivot('employment_type', 'active', 'commission_rate')
            ->withTimestamps();
    }

    /**
     * Get company shipments
     */
    public function shipments()
    {
        return $this->hasMany(\App\Models\Shipment::class, 'company_id');
    }

    /**
     * Get pending shipments
     */
    public function pendingShipments()
    {
        return $this->shipments()->where('status', 'pending');
    }

    /**
     * Get active shipments
     */
    public function activeShipments()
    {
        return $this->shipments()->whereIn('status', ['accepted', 'picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Get wallet merchant account
     */
    public function walletMerchant()
    {
        return $this->belongsTo(\App\Models\WalletMerchant::class, 'wallet_merchant_id');
    }

    /**
     * Calculate success rate
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_shipments == 0) {
            return 0;
        }

        return round(($this->completed_shipments / $this->total_shipments) * 100, 2);
    }

    /**
     * Check if company serves a specific city
     */
    public function servesCity($city)
    {
        if (is_null($this->service_cities)) {
            return false;
        }

        return in_array($city, $this->service_cities);
    }

    /**
     * Calculate shipping cost based on pricing model
     */
    public function calculateShippingCost($distance = 0, $weight = 0)
    {
        switch ($this->pricing_model) {
            case 'per_km':
                return $this->base_price + ($distance * $this->price_per_km);

            case 'per_weight':
                return $this->base_price + ($weight * $this->price_per_kg);

            case 'flat_rate':
                return $this->base_price;

            default: // custom
                return $this->base_price;
        }
    }
}
