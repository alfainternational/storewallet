<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EscrowTransaction extends Model {
    protected $fillable = ['order_id', 'amount', 'status', 'held_at', 'released_at', 'refunded_at'];
    protected $casts = ['amount' => 'decimal:2', 'held_at' => 'datetime', 'released_at' => 'datetime', 'refunded_at' => 'datetime'];
    public function order() { return $this->belongsTo(Order::class); }
}
