<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Shipment
 * @package App\Models
 */
class Shipment extends Model
{
    use SoftDeletes;

    public $table = 'shipments';

    public $fillable = [
        'tracking_number',
        'order_id',
        'customer_user_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_city',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'receiver_city',
        'receiver_country',
        'package_type',
        'weight',
        'length',
        'width',
        'height',
        'declared_value',
        'package_description',
        'fragile',
        'insurance',
        'company_id',
        'driver_id',
        'shipping_cost',
        'insurance_cost',
        'total_cost',
        'company_earning',
        'driver_earning',
        'platform_fee',
        'status',
        'accepted_at',
        'picked_up_at',
        'in_transit_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
        'delivery_signature',
        'delivery_photo',
        'delivery_notes',
        'rating',
        'review',
        'payment_method',
        'paid',
        'escrow_trx'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'fragile' => 'boolean',
        'insurance' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'company_earning' => 'decimal:2',
        'driver_earning' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'paid' => 'boolean',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get customer
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_user_id');
    }

    /**
     * Get linked order
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    /**
     * Get shipping company
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\ShippingCompany::class, 'company_id');
    }

    /**
     * Get assigned driver
     */
    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    /**
     * Get tracking history
     */
    public function tracking()
    {
        return $this->hasMany(\App\Models\ShipmentTracking::class, 'shipment_id')->orderBy('created_at', 'desc');
    }

    /**
     * Generate unique tracking number
     */
    public static function generateTrackingNumber()
    {
        do {
            $trackingNumber = 'SHP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        } while (self::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }

    /**
     * Add tracking update
     */
    public function addTracking($status, $location = null, $notes = null, $latitude = null, $longitude = null)
    {
        return $this->tracking()->create([
            'status' => $status,
            'location' => $location,
            'notes' => $notes,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'updated_by_user_id' => auth()->id()
        ]);
    }

    /**
     * Update shipment status
     */
    public function updateStatus($newStatus, $location = null, $notes = null)
    {
        $this->status = $newStatus;

        // Update timestamp fields
        $timestampField = $newStatus . '_at';
        if (in_array($timestampField, $this->fillable)) {
            $this->$timestampField = now();
        }

        $this->save();

        // Add to tracking history
        $this->addTracking($newStatus, $location, $notes);

        return $this;
    }

    /**
     * Check if shipment is active
     */
    public function isActive()
    {
        return in_array($this->status, ['pending', 'accepted', 'picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Check if delivered
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
