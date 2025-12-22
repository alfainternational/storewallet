<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingCompany extends Model {
    protected $fillable = ['user_id', 'company_name', 'license_number', 'vehicle_count', 'service_cities', 'rating', 'is_verified'];
    protected $casts = ['service_cities' => 'array', 'rating' => 'decimal:2', 'is_verified' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
    public function shipments() { return $this->hasMany(Shipment::class); }
}
