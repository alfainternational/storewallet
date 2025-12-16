<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class ShipmentTracking
 * @package App\Models
 */
class ShipmentTracking extends Model
{
    public $table = 'shipment_tracking';

    public $fillable = [
        'shipment_id',
        'status',
        'location',
        'latitude',
        'longitude',
        'notes',
        'updated_by_user_id'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    /**
     * Get shipment
     */
    public function shipment()
    {
        return $this->belongsTo(\App\Models\Shipment::class, 'shipment_id');
    }

    /**
     * Get user who made the update
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
