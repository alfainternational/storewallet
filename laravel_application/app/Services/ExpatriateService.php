<?php

namespace App\Services;

use App\Models\User;
use App\Models\Auction;
use App\Models\Order;
use App\Models\Currency;
use App\Models\SudaneseCity;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service for handling expatriate-specific features
 */
class ExpatriateService
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Mark user as expatriate and set their location
     *
     * @param int $userId
     * @param string $countryCode
     * @param string $currencyCode
     * @return bool
     */
    public function registerAsExpatriate($userId, $countryCode, $currencyCode = null)
    {
        $user = User::findOrFail($userId);

        $updateData = [
            'is_expatriate' => true,
            'expatriate_location' => $countryCode,
            'country_code' => $countryCode
        ];

        if ($currencyCode) {
            $currency = Currency::getByCode($currencyCode);
            if ($currency) {
                $updateData['preferred_currency'] = $currencyCode;
            }
        }

        return $user->update($updateData);
    }

    /**
     * Get international shipping auctions available for expatriates
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getInternationalShippingAuctions($filters = [])
    {
        $query = Auction::where('type', 'international')
            ->where('status', 'active')
            ->where('is_published', true);

        // Filter by origin country (Sudan)
        if (isset($filters['origin_country'])) {
            $query->where('origin_country', $filters['origin_country']);
        }

        // Filter by destination country
        if (isset($filters['destination_country'])) {
            $query->where('destination_country', $filters['destination_country']);
        }

        // Filter by package type
        if (isset($filters['package_type'])) {
            $query->where('package_type', $filters['package_type']);
        }

        // Filter by weight range
        if (isset($filters['max_weight'])) {
            $query->where('package_weight', '<=', $filters['max_weight']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Create international shipping request
     *
     * @param array $data
     * @return Auction
     */
    public function createInternationalShippingRequest($data)
    {
        DB::beginTransaction();

        try {
            // Validate required fields for international shipping
            $this->validateInternationalShippingData($data);

            $auctionData = [
                'user_id' => auth()->id(),
                'type' => 'international',
                'is_reverse_auction' => true,
                'title' => $data['title'] ?? 'International Shipping Request',
                'description' => $data['description'],
                'origin_country' => $data['origin_country'],
                'origin_city' => $data['origin_city'] ?? null,
                'destination_country' => $data['destination_country'],
                'destination_city' => $data['destination_city'] ?? null,
                'pickup_address' => $data['pickup_address'],
                'delivery_address' => $data['delivery_address'],
                'package_type' => $data['package_type'],
                'package_weight' => $data['package_weight'],
                'package_dimensions' => $data['package_dimensions'] ?? null,
                'customs_declaration' => $data['customs_declaration'] ?? null,
                'requires_customs_clearance' => $data['requires_customs_clearance'] ?? true,
                'estimated_customs_value' => $data['estimated_customs_value'] ?? null,
                'starting_price' => $data['starting_price'],
                'currency_code' => $data['currency_code'] ?? 'USD',
                'duration_minutes' => $data['duration_minutes'] ?? 10080, // 7 days default
                'status' => 'draft'
            ];

            $auction = Auction::create($auctionData);

            DB::commit();

            return $auction;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate international shipping data
     *
     * @param array $data
     * @throws Exception
     */
    protected function validateInternationalShippingData($data)
    {
        $required = [
            'description',
            'origin_country',
            'destination_country',
            'pickup_address',
            'delivery_address',
            'package_type',
            'package_weight',
            'starting_price'
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Field {$field} is required for international shipping");
            }
        }

        // Validate package types
        $validPackageTypes = ['documents', 'parcel', 'luggage', 'commercial', 'personal_effects'];
        if (!in_array($data['package_type'], $validPackageTypes)) {
            throw new Exception("Invalid package type");
        }

        // Validate weight
        if ($data['package_weight'] <= 0 || $data['package_weight'] > 1000) {
            throw new Exception("Package weight must be between 0 and 1000 kg");
        }
    }

    /**
     * Get popular expatriate destinations for Sudanese people
     *
     * @return array
     */
    public function getPopularExpatriateDestinations()
    {
        return [
            ['code' => 'SA', 'name_en' => 'Saudi Arabia', 'name_ar' => 'السعودية', 'currency' => 'SAR'],
            ['code' => 'AE', 'name_en' => 'United Arab Emirates', 'name_ar' => 'الإمارات', 'currency' => 'AED'],
            ['code' => 'QA', 'name_en' => 'Qatar', 'name_ar' => 'قطر', 'currency' => 'QAR'],
            ['code' => 'KW', 'name_en' => 'Kuwait', 'name_ar' => 'الكويت', 'currency' => 'KWD'],
            ['code' => 'EG', 'name_en' => 'Egypt', 'name_ar' => 'مصر', 'currency' => 'EGP'],
            ['code' => 'GB', 'name_en' => 'United Kingdom', 'name_ar' => 'بريطانيا', 'currency' => 'GBP'],
            ['code' => 'US', 'name_en' => 'United States', 'name_ar' => 'الولايات المتحدة', 'currency' => 'USD'],
            ['code' => 'CA', 'name_en' => 'Canada', 'name_ar' => 'كندا', 'currency' => 'CAD'],
            ['code' => 'AU', 'name_en' => 'Australia', 'name_ar' => 'أستراليا', 'currency' => 'AUD'],
            ['code' => 'DE', 'name_en' => 'Germany', 'name_ar' => 'ألمانيا', 'currency' => 'EUR'],
        ];
    }

    /**
     * Calculate shipping cost with currency conversion
     *
     * @param float $basePrice
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return array
     */
    public function calculateInternationalShippingCost($basePrice, $fromCurrency, $toCurrency = 'SDG')
    {
        $convertedPrice = $this->currencyService->convert($basePrice, $fromCurrency, $toCurrency);

        return [
            'original_price' => $basePrice,
            'original_currency' => $fromCurrency,
            'converted_price' => $convertedPrice,
            'converted_currency' => $toCurrency,
            'exchange_rate' => $this->currencyService->getExchangeRate($fromCurrency, $toCurrency)
        ];
    }

    /**
     * Get products available for international delivery
     *
     * @param string $destinationCountry
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProductsForInternationalDelivery($destinationCountry, $filters = [])
    {
        $query = \App\Models\Product::where('active', true)
            ->where('international_shipping_available', true);

        // Filter by destination country availability
        if ($destinationCountry) {
            $query->where(function($q) use ($destinationCountry) {
                $q->whereNull('shipping_countries')
                  ->orWhereJsonContains('shipping_countries', $destinationCountry);
            });
        }

        // Apply other filters (category, price range, etc.)
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get expatriate user statistics
     *
     * @param int $userId
     * @return array
     */
    public function getExpatriateStats($userId)
    {
        $user = User::findOrFail($userId);

        if (!$user->is_expatriate) {
            return [];
        }

        $stats = [
            'total_international_orders' => Order::where('user_id', $userId)
                ->where('is_international', true)
                ->count(),

            'total_spent_local_currency' => 0,
            'total_spent_sdg' => 0,

            'active_shipping_requests' => Auction::where('user_id', $userId)
                ->where('type', 'international')
                ->where('status', 'active')
                ->count(),

            'completed_shipments' => Auction::where('user_id', $userId)
                ->where('type', 'international')
                ->where('status', 'completed')
                ->count(),

            'preferred_currency' => $user->preferred_currency,
            'location' => $user->expatriate_location
        ];

        return $stats;
    }

    /**
     * Check if a user can access expatriate features
     *
     * @param int|null $userId
     * @return bool
     */
    public function canAccessExpatriateFeatures($userId = null)
    {
        if (!$userId) {
            return false;
        }

        $user = User::find($userId);
        return $user && $user->is_expatriate;
    }

    /**
     * Get recommended shipping companies for international route
     *
     * @param string $originCountry
     * @param string $destinationCountry
     * @return \Illuminate\Support\Collection
     */
    public function getRecommendedShippingCompanies($originCountry, $destinationCountry)
    {
        return \App\Models\ShippingCompany::where('verified', true)
            ->where('active', true)
            ->whereIn('service_type', ['international', 'all'])
            ->where(function($q) use ($originCountry, $destinationCountry) {
                $q->whereJsonContains('service_countries', $originCountry)
                  ->whereJsonContains('service_countries', $destinationCountry);
            })
            ->orderBy('rating', 'desc')
            ->get();
    }
}
