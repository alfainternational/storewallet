<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SudaneseCity extends Model {
    protected $fillable = ['name', 'name_ar', 'state', 'latitude', 'longitude'];
}
