<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Shipment extends Model {
    protected $fillable = ['order_id', 'shipping_company_id', 'tracking_number', 'status', 'pickup_lat', 'pickup_lng', 'delivery_lat', 'delivery_lng', 'current_lat', 'current_lng', 'estimated_delivery'];
    protected $casts = ['estimated_delivery' => 'datetime'];
    public function order() { return $this->belongsTo(Order::class); }
    public function shippingCompany() { return $this->belongsTo(ShippingCompany::class); }
}
