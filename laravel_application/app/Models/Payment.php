<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Payment
 * @package App\Models
 *
 * @property integer id
 * @property string description
 * @property double price
 * @property string status
 * @property string method
 * @property string escrow_trx
 * @property string escrow_status
 * @property double escrow_amount
 * @property string release_trx
 * @property string refund_trx
 */
class Payment extends Model
{
    public $table = 'payments';

    public $fillable = [
        'description',
        'price',
        'user_id',
        'method',
        'status',
        'escrow_trx',
        'escrow_status',
        'escrow_amount',
        'release_trx',
        'released_at',
        'refund_trx',
        'refunded_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'double',
        'escrow_amount' => 'double',
        'status' => 'string',
        'method' => 'string',
        'escrow_status' => 'string',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'price' => 'required|numeric|min:0',
        'method' => 'required|string'
    ];

    /**
     * Get the order that owns the payment
     */
    public function order()
    {
        return $this->hasOne(\App\Models\Order::class, 'payment_id', 'id');
    }

    /**
     * Get the user who made the payment
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * Check if payment is in escrow
     */
    public function isInEscrow()
    {
        return $this->escrow_status === 'held';
    }

    /**
     * Check if payment is released
     */
    public function isReleased()
    {
        return $this->escrow_status === 'released';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded()
    {
        return $this->escrow_status === 'refunded';
    }
}
