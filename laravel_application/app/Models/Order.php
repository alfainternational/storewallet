<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status', // pending, processing, shipped, delivered, cancelled, refunded
        'payment_status', // pending, paid, failed, refunded
        'payment_method', // wallet, bankak, e15, sudanipay, stripe, cash_on_delivery
        'subtotal',
        'shipping_cost',
        'tax',
        'discount',
        'total',
        'shipping_address',
        'shipping_city_id',
        'shipping_phone',
        'notes',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingCity()
    {
        return $this->belongsTo(SudaneseCity::class, 'shipping_city_id');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function escrowTransaction()
    {
        return $this->hasOne(EscrowTransaction::class);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'معلق',
            'paid' => 'مدفوع',
            'failed' => 'فشل',
            'refunded' => 'مسترد',
        ];

        return $labels[$this->payment_status] ?? $this->payment_status;
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'delivered';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsRefundedAttribute()
    {
        return $this->status === 'refunded' || $this->payment_status === 'refunded';
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    // Methods
    public function markAsPaid()
    {
        $this->update(['payment_status' => 'paid']);
    }

    public function markAsShipped($trackingNumber = null)
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'tracking_number' => $trackingNumber,
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }
}
