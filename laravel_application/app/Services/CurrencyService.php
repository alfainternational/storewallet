<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Get the default currency (SDG)
     *
     * @return Currency
     */
    public function getDefaultCurrency()
    {
        return Cache::remember('default_currency', 3600, function () {
            return Currency::getDefault() ?? Currency::getByCode('SDG');
        });
    }

    /**
     * Get user's preferred currency based on their location or settings
     *
     * @param int|null $userId
     * @return Currency
     */
    public function getUserCurrency($userId = null)
    {
        // If user is authenticated and has currency preference
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user && $user->preferred_currency) {
                $currency = Currency::getByCode($user->preferred_currency);
                if ($currency) {
                    return $currency;
                }
            }
        }

        // Fall back to default currency
        return $this->getDefaultCurrency();
    }

    /**
     * Convert amount between currencies
     *
     * @param float $amount
     * @param string $fromCode
     * @param string $toCode
     * @return float
     */
    public function convert($amount, $fromCode, $toCode)
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $fromCurrency = Currency::getByCode($fromCode);
        $toCurrency = Currency::getByCode($toCode);

        if (!$fromCurrency || !$toCurrency) {
            Log::warning("Currency conversion failed: {$fromCode} to {$toCode}");
            return $amount;
        }

        return $fromCurrency->convertTo($amount, $toCode);
    }

    /**
     * Format amount with currency
     *
     * @param float $amount
     * @param string $currencyCode
     * @param bool $useArabic
     * @return string
     */
    public function format($amount, $currencyCode, $useArabic = null)
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency) {
            return number_format($amount, 2);
        }

        if ($useArabic === null) {
            return $currency->formatLocalized($amount);
        }

        return $currency->format($amount, $useArabic);
    }

    /**
     * Get all active currencies for selection
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveCurrencies()
    {
        return Cache::remember('active_currencies', 3600, function () {
            return Currency::getActive();
        });
    }

    /**
     * Get currencies commonly used by Sudanese expatriates
     *
     * @return \Illuminate\Support\Collection
     */
    public function getExpatriateCurrencies()
    {
        $codes = ['SDG', 'USD', 'SAR', 'AED', 'QAR', 'KWD', 'EUR', 'GBP', 'CAD', 'EGP'];

        return Currency::whereIn('code', $codes)
            ->where('active', true)
            ->orderByRaw("FIELD(code, 'SDG', 'USD', 'SAR', 'AED', 'EUR', 'GBP')")
            ->get();
    }

    /**
     * Update exchange rates (can be called from scheduled task)
     * Note: In production, integrate with a real exchange rate API
     *
     * @return array
     */
    public function updateExchangeRates()
    {
        $updated = [];
        $failed = [];

        // In production, you would fetch from an API like:
        // - https://exchangeratesapi.io/
        // - https://openexchangerates.org/
        // - Central Bank of Sudan API if available

        // For now, log that manual update is needed
        Log::info('Exchange rate update triggered. Manual rates or API integration needed.');

        // Example: Update SDG rate if older than 24 hours
        $sdg = Currency::getByCode('SDG');
        if ($sdg && $sdg->needsRateUpdate()) {
            // In production, fetch actual rate from API
            // For now, we'll skip automatic update and log
            Log::warning('SDG exchange rate needs update. Please update manually or integrate with exchange rate API.');
            $failed[] = 'SDG';
        }

        return [
            'updated' => $updated,
            'failed' => $failed,
            'message' => 'Exchange rate update requires API integration or manual update.'
        ];
    }

    /**
     * Manually update a currency's exchange rate
     *
     * @param string $currencyCode
     * @param float $rateToUsd
     * @return bool
     */
    public function updateCurrencyRate($currencyCode, $rateToUsd)
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency) {
            return false;
        }

        $result = $currency->updateExchangeRate($rateToUsd);

        // Clear cache
        Cache::forget('active_currencies');
        if ($currency->is_default) {
            Cache::forget('default_currency');
        }

        Log::info("Currency rate updated: {$currencyCode} = {$rateToUsd} USD");

        return $result;
    }

    /**
     * Get exchange rate between two currencies
     *
     * @param string $fromCode
     * @param string $toCode
     * @return float|null
     */
    public function getExchangeRate($fromCode, $toCode)
    {
        if ($fromCode === $toCode) {
            return 1.0;
        }

        $fromCurrency = Currency::getByCode($fromCode);
        $toCurrency = Currency::getByCode($toCode);

        if (!$fromCurrency || !$toCurrency) {
            return null;
        }

        // Convert 1 unit from source to target
        return $fromCurrency->convertTo(1, $toCode);
    }

    /**
     * Calculate wallet transaction in SDG
     * This ensures all wallet transactions use the default currency
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function convertToWalletCurrency($amount, $currencyCode)
    {
        $defaultCurrency = $this->getDefaultCurrency();
        return $this->convert($amount, $currencyCode, $defaultCurrency->code);
    }

    /**
     * Format price for display with automatic currency selection
     *
     * @param float $amount
     * @param string|null $currencyCode
     * @return string
     */
    public function formatPrice($amount, $currencyCode = null)
    {
        if (!$currencyCode) {
            $currencyCode = $this->getDefaultCurrency()->code;
        }

        return $this->format($amount, $currencyCode);
    }
}
