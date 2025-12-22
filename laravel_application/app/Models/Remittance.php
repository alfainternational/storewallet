<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remittance extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'remittance_number',
        'sender_name',
        'recipient_name',
        'recipient_phone',
        'recipient_city_id',
        'amount',
        'currency_id',
        'exchange_rate',
        'amount_in_sdg',
        'fees',
        'total_amount',
        'receiving_method', // cash_pickup, wallet, bank_transfer
        'pickup_code',
        'status', // pending, ready_for_pickup, completed, cancelled
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_in_sdg' => 'decimal:2',
        'fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function recipientCity()
    {
        return $this->belongsTo(SudaneseCity::class, 'recipient_city_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
