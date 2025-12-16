<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Remittance
 * @package App\Models
 */
class Remittance extends Model
{
    use SoftDeletes;

    public $table = 'remittances';

    public $fillable = [
        'remittance_number',
        'sender_user_id',
        'sender_name',
        'sender_country',
        'sender_phone',
        'sender_email',
        'receiver_user_id',
        'receiver_name',
        'receiver_phone',
        'receiver_email',
        'receiver_national_id',
        'receiver_city',
        'receiver_address',
        'send_amount',
        'send_currency',
        'exchange_rate',
        'receive_amount',
        'receive_currency',
        'service_fee',
        'fee_currency',
        'total_charged',
        'delivery_method',
        'bank_name',
        'bank_account_number',
        'mobile_money_provider',
        'mobile_money_number',
        'pickup_location',
        'pickup_code',
        'status',
        'status_notes',
        'approved_at',
        'completed_at',
        'cancelled_at',
        'sender_transaction_id',
        'receiver_transaction_id',
        'payment_reference',
        'external_reference',
        'purpose',
        'compliance_checked',
        'compliance_notes',
        'requires_verification',
        'verification_document',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'send_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'receive_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_charged' => 'decimal:2',
        'compliance_checked' => 'boolean',
        'requires_verification' => 'boolean',
        'metadata' => 'array',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    /**
     * Generate unique remittance number
     *
     * @return string
     */
    public static function generateRemittanceNumber()
    {
        $prefix = 'REM';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get sender user
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Get receiver user
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    /**
     * Get send currency
     */
    public function sendCurrency()
    {
        return $this->belongsTo(Currency::class, 'send_currency', 'code');
    }

    /**
     * Get receive currency
     */
    public function receiveCurrency()
    {
        return $this->belongsTo(Currency::class, 'receive_currency', 'code');
    }

    /**
     * Get status history
     */
    public function statusHistory()
    {
        return $this->hasMany(RemittanceStatusHistory::class, 'remittance_id');
    }

    /**
     * Update status with history tracking
     *
     * @param string $newStatus
     * @param string|null $notes
     * @param int|null $updatedBy
     * @return bool
     */
    public function updateStatus($newStatus, $notes = null, $updatedBy = null)
    {
        // Record status history
        RemittanceStatusHistory::create([
            'remittance_id' => $this->id,
            'status' => $newStatus,
            'notes' => $notes,
            'updated_by' => $updatedBy ?? auth()->id(),
            'created_at' => now()
        ]);

        // Update status
        $this->status = $newStatus;

        // Update timestamps based on status
        if ($newStatus === 'approved') {
            $this->approved_at = now();
        } elseif ($newStatus === 'completed') {
            $this->completed_at = now();
        } elseif ($newStatus === 'cancelled') {
            $this->cancelled_at = now();
        }

        return $this->save();
    }

    /**
     * Check if remittance can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if remittance is completed
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if remittance is pending
     *
     * @return bool
     */
    public function isPending()
    {
        return in_array($this->status, ['pending', 'processing', 'approved', 'in_transit', 'ready_for_pickup']);
    }

    /**
     * Generate pickup code for cash pickup
     *
     * @return string
     */
    public static function generatePickupCode()
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    /**
     * Get formatted send amount
     *
     * @return string
     */
    public function getFormattedSendAmountAttribute()
    {
        $currency = $this->sendCurrency;
        return $currency ? $currency->formatLocalized($this->send_amount) : $this->send_amount;
    }

    /**
     * Get formatted receive amount
     *
     * @return string
     */
    public function getFormattedReceiveAmountAttribute()
    {
        $currency = $this->receiveCurrency;
        return $currency ? $currency->formatLocalized($this->receive_amount) : $this->receive_amount;
    }

    /**
     * Get status badge color
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'approved' => 'primary',
            'in_transit' => 'info',
            'ready_for_pickup' => 'success',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'failed' => 'danger',
            'refunded' => 'warning'
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}
