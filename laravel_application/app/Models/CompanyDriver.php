<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class CompanyDriver
 * @package App\Models
 * Pivot model for shipping companies and drivers relationship
 */
class CompanyDriver extends Model
{
    public $table = 'company_drivers';

    public $fillable = [
        'company_id',
        'driver_id',
        'employment_type',
        'joined_date',
        'left_date',
        'active',
        'commission_rate'
    ];

    protected $casts = [
        'active' => 'boolean',
        'commission_rate' => 'decimal:2',
        'joined_date' => 'date',
        'left_date' => 'date'
    ];

    /**
     * Get company
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\ShippingCompany::class, 'company_id');
    }

    /**
     * Get driver
     */
    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    /**
     * Get driver's user account
     */
    public function user()
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            \App\Models\Driver::class,
            'id',           // Foreign key on drivers table
            'id',           // Foreign key on users table
            'driver_id',    // Local key on company_drivers table
            'user_id'       // Local key on drivers table
        );
    }
}
