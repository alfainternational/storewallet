<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Currency extends Model {
    protected $fillable = ['code', 'name', 'name_ar', 'symbol', 'exchange_rate_to_sdg'];
    protected $casts = ['exchange_rate_to_sdg' => 'decimal:4'];
}
