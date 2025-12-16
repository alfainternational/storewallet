<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Currency
 * @package App\Models
 */
class Currency extends Model
{
    public $table = 'currencies';

    public $fillable = [
        'code',
        'name',
        'name_ar',
        'symbol',
        'symbol_ar',
        'exchange_rate_to_usd',
        'decimal_places',
        'is_default',
        'active',
        'rate_updated_at'
    ];

    protected $casts = [
        'exchange_rate_to_usd' => 'decimal:6',
        'decimal_places' => 'integer',
        'is_default' => 'boolean',
        'active' => 'boolean',
        'rate_updated_at' => 'datetime'
    ];

    /**
     * Get the default currency
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->where('active', true)->first();
    }

    /**
     * Get currency by code
     */
    public static function getByCode($code)
    {
        return self::where('code', $code)->where('active', true)->first();
    }

    /**
     * Get all active currencies
     */
    public static function getActive()
    {
        return self::where('active', true)->orderBy('is_default', 'desc')->orderBy('code')->get();
    }

    /**
     * Convert amount from this currency to another currency
     *
     * @param float $amount
     * @param string $toCurrencyCode
     * @return float
     */
    public function convertTo($amount, $toCurrencyCode)
    {
        $toCurrency = self::getByCode($toCurrencyCode);

        if (!$toCurrency) {
            return $amount;
        }

        // Convert to USD first
        $amountInUsd = $amount / $this->exchange_rate_to_usd;

        // Then convert to target currency
        $convertedAmount = $amountInUsd * $toCurrency->exchange_rate_to_usd;

        return round($convertedAmount, $toCurrency->decimal_places);
    }

    /**
     * Convert amount from another currency to this currency
     *
     * @param float $amount
     * @param string $fromCurrencyCode
     * @return float
     */
    public function convertFrom($amount, $fromCurrencyCode)
    {
        $fromCurrency = self::getByCode($fromCurrencyCode);

        if (!$fromCurrency) {
            return $amount;
        }

        return $fromCurrency->convertTo($amount, $this->code);
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @param bool $useArabic
     * @return string
     */
    public function format($amount, $useArabic = false)
    {
        $formatted = number_format($amount, $this->decimal_places, '.', ',');

        $symbol = $useArabic && $this->symbol_ar ? $this->symbol_ar : $this->symbol;

        // For Arabic, symbol goes after amount
        if ($useArabic) {
            return $formatted . ' ' . $symbol;
        }

        // For English, symbol goes before amount (except for some currencies)
        if (in_array($this->code, ['SAR', 'AED', 'QAR', 'KWD', 'EGP'])) {
            return $formatted . ' ' . $symbol;
        }

        return $symbol . ' ' . $formatted;
    }

    /**
     * Format amount with currency symbol based on current locale
     *
     * @param float $amount
     * @return string
     */
    public function formatLocalized($amount)
    {
        $locale = app()->getLocale();
        return $this->format($amount, $locale === 'ar');
    }

    /**
     * Get localized currency name
     *
     * @return string
     */
    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    /**
     * Get localized currency symbol
     *
     * @return string
     */
    public function getLocalizedSymbolAttribute()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->symbol_ar ? $this->symbol_ar : $this->symbol;
    }

    /**
     * Update exchange rate
     *
     * @param float $newRate
     * @return bool
     */
    public function updateExchangeRate($newRate)
    {
        $this->exchange_rate_to_usd = $newRate;
        $this->rate_updated_at = now();
        return $this->save();
    }

    /**
     * Check if exchange rate needs update (older than 24 hours)
     *
     * @return bool
     */
    public function needsRateUpdate()
    {
        if (!$this->rate_updated_at) {
            return true;
        }

        return $this->rate_updated_at->diffInHours(now()) > 24;
    }
}
