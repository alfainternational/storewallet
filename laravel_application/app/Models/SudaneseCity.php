<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SudaneseCity
 * @package App\Models
 */
class SudaneseCity extends Model
{
    public $table = 'sudanese_cities';

    public $fillable = [
        'name_ar',
        'name_en',
        'state_id',
        'state_name_ar',
        'state_name_en',
        'latitude',
        'longitude',
        'is_major_city',
        'has_airport',
        'has_port',
        'population',
        'active'
    ];

    protected $casts = [
        'state_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_major_city' => 'boolean',
        'has_airport' => 'boolean',
        'has_port' => 'boolean',
        'population' => 'integer',
        'active' => 'boolean'
    ];

    /**
     * Get all major cities
     */
    public static function getMajorCities()
    {
        return self::where('is_major_city', true)
            ->where('active', true)
            ->orderBy('population', 'desc')
            ->get();
    }

    /**
     * Get cities by state
     */
    public static function getCitiesByState($stateId)
    {
        return self::where('state_id', $stateId)
            ->where('active', true)
            ->orderBy('population', 'desc')
            ->get();
    }

    /**
     * Get cities with airports
     */
    public static function getCitiesWithAirports()
    {
        return self::where('has_airport', true)
            ->where('active', true)
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get cities with ports
     */
    public static function getCitiesWithPorts()
    {
        return self::where('has_port', true)
            ->where('active', true)
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get all states (grouped)
     */
    public static function getAllStates()
    {
        return self::where('active', true)
            ->select('state_id', 'state_name_ar', 'state_name_en')
            ->distinct()
            ->orderBy('state_id')
            ->get();
    }

    /**
     * Search cities by name (Arabic or English)
     */
    public static function search($query)
    {
        return self::where('active', true)
            ->where(function($q) use ($query) {
                $q->where('name_ar', 'LIKE', "%{$query}%")
                  ->orWhere('name_en', 'LIKE', "%{$query}%");
            })
            ->orderBy('population', 'desc')
            ->get();
    }

    /**
     * Calculate distance from coordinates (simplified Haversine)
     */
    public function distanceFrom($lat, $lon)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat);
        $lonFrom = deg2rad($lon);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Get localized name based on current locale
     */
    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get full location string
     */
    public function getFullLocationAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return "{$this->name_ar}, {$this->state_name_ar}";
        }
        return "{$this->name_en}, {$this->state_name_en}";
    }
}
