<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrenciesSeeder extends Seeder
{
    public function run()
    {
        $currencies = [
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'name_ar' => 'الجنيه السوداني', 'symbol' => 'SDG', 'exchange_rate' => 1.0000, 'is_active' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'name_ar' => 'الدولار الأمريكي', 'symbol' => '$', 'exchange_rate' => 600.0000, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'name_ar' => 'اليورو', 'symbol' => '€', 'exchange_rate' => 650.0000, 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'name_ar' => 'الجنيه الإسترليني', 'symbol' => '£', 'exchange_rate' => 750.0000, 'is_active' => true],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'name_ar' => 'الريال السعودي', 'symbol' => 'SR', 'exchange_rate' => 160.0000, 'is_active' => true],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'name_ar' => 'الدرهم الإماراتي', 'symbol' => 'AED', 'exchange_rate' => 163.0000, 'is_active' => true],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'name_ar' => 'الريال القطري', 'symbol' => 'QR', 'exchange_rate' => 165.0000, 'is_active' => true],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'name_ar' => 'الدينار الكويتي', 'symbol' => 'KD', 'exchange_rate' => 1950.0000, 'is_active' => true],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'name_ar' => 'الجنيه المصري', 'symbol' => 'E£', 'exchange_rate' => 19.5000, 'is_active' => true],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'name_ar' => 'الدولار الكندي', 'symbol' => 'C$', 'exchange_rate' => 440.0000, 'is_active' => true],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}
