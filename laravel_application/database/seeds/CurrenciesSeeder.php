<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            // Sudanese Pound - Primary Currency
            [
                'code' => 'SDG',
                'name' => 'Sudanese Pound',
                'name_ar' => 'الجنيه السوداني',
                'symbol' => 'SDG',
                'symbol_ar' => 'ج.س',
                'exchange_rate_to_usd' => 600.00, // Approximate rate (fluctuates significantly)
                'decimal_places' => 2,
                'is_default' => true,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // US Dollar - Widely used for international transactions
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'name_ar' => 'الدولار الأمريكي',
                'symbol' => '$',
                'symbol_ar' => '$',
                'exchange_rate_to_usd' => 1.00,
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Euro - Used by expatriates in Europe
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'name_ar' => 'اليورو',
                'symbol' => '€',
                'symbol_ar' => '€',
                'exchange_rate_to_usd' => 0.92, // Approximate rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Saudi Riyal - Major expatriate community in Saudi Arabia
            [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'name_ar' => 'الريال السعودي',
                'symbol' => 'SR',
                'symbol_ar' => 'ر.س',
                'exchange_rate_to_usd' => 3.75, // Fixed rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // UAE Dirham - Expatriates in UAE
            [
                'code' => 'AED',
                'name' => 'UAE Dirham',
                'name_ar' => 'الدرهم الإماراتي',
                'symbol' => 'AED',
                'symbol_ar' => 'د.إ',
                'exchange_rate_to_usd' => 3.67, // Fixed rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Egyptian Pound - Neighboring country, trade relations
            [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'name_ar' => 'الجنيه المصري',
                'symbol' => 'E£',
                'symbol_ar' => 'ج.م',
                'exchange_rate_to_usd' => 30.90, // Approximate rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Qatari Riyal - Expatriates in Qatar
            [
                'code' => 'QAR',
                'name' => 'Qatari Riyal',
                'name_ar' => 'الريال القطري',
                'symbol' => 'QR',
                'symbol_ar' => 'ر.ق',
                'exchange_rate_to_usd' => 3.64, // Fixed rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Kuwaiti Dinar - Expatriates in Kuwait
            [
                'code' => 'KWD',
                'name' => 'Kuwaiti Dinar',
                'name_ar' => 'الدينار الكويتي',
                'symbol' => 'KD',
                'symbol_ar' => 'د.ك',
                'exchange_rate_to_usd' => 0.31, // Approximate rate
                'decimal_places' => 3,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // British Pound - Expatriates in UK
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'name_ar' => 'الجنيه الإسترليني',
                'symbol' => '£',
                'symbol_ar' => '£',
                'exchange_rate_to_usd' => 0.79, // Approximate rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],

            // Canadian Dollar - Expatriates in Canada
            [
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'name_ar' => 'الدولار الكندي',
                'symbol' => 'C$',
                'symbol_ar' => 'C$',
                'exchange_rate_to_usd' => 1.36, // Approximate rate
                'decimal_places' => 2,
                'is_default' => false,
                'active' => true,
                'rate_updated_at' => now()
            ],
        ];

        DB::table('currencies')->insert($currencies);
    }
}
