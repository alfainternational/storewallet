<?php

use App\Models\Currency;
use App\Models\SudaneseCity;
use Illuminate\Support\Str;

if (!function_exists('formatMoney')) {
    /**
     * تنسيق المبلغ المالي
     *
     * @param float $amount
     * @param string $currencyCode
     * @return string
     */
    function formatMoney($amount, $currencyCode = 'SDG')
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency) {
            return number_format($amount, 2);
        }

        return $currency->formatLocalized($amount);
    }
}

if (!function_exists('convertCurrency')) {
    /**
     * تحويل العملة
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    function convertCurrency($amount, $from, $to = 'SDG')
    {
        $service = app(\App\Services\CurrencyService::class);
        return $service->convert($amount, $from, $to);
    }
}

if (!function_exists('sendSMS')) {
    /**
     * إرسال رسالة SMS
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    function sendSMS($phone, $message)
    {
        $service = app(\App\Services\NotificationService::class);
        return $service->sendSMS($phone, $message);
    }
}

if (!function_exists('sendNotification')) {
    /**
     * إرسال إشعار كامل
     *
     * @param int $userId
     * @param string $type
     * @param array $data
     * @return array
     */
    function sendNotification($userId, $type, $data = [])
    {
        $service = app(\App\Services\NotificationService::class);
        return $service->sendNotification($userId, $type, $data);
    }
}

if (!function_exists('getCityName')) {
    /**
     * الحصول على اسم المدينة
     *
     * @param int $cityId
     * @param string $locale
     * @return string|null
     */
    function getCityName($cityId, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $city = SudaneseCity::find($cityId);

        if (!$city) {
            return null;
        }

        return $locale === 'ar' ? $city->name_ar : $city->name_en;
    }
}

if (!function_exists('isArabic')) {
    /**
     * التحقق من اللغة العربية
     *
     * @return bool
     */
    function isArabic()
    {
        return app()->getLocale() === 'ar';
    }
}

if (!function_exists('rtl')) {
    /**
     * التحقق من اتجاه الكتابة
     *
     * @return bool
     */
    function rtl()
    {
        return in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    }
}

if (!function_exists('direction')) {
    /**
     * الحصول على اتجاه الكتابة
     *
     * @return string
     */
    function direction()
    {
        return rtl() ? 'rtl' : 'ltr';
    }
}

if (!function_exists('trans_choice_arabic')) {
    /**
     * ترجمة مع دعم الجمع العربي
     *
     * @param string $key
     * @param int $count
     * @param array $replace
     * @return string
     */
    function trans_choice_arabic($key, $count, $replace = [])
    {
        if (!isArabic()) {
            return trans_choice($key, $count, $replace);
        }

        // قواعد الجمع العربي
        if ($count == 0) {
            $suffix = 'zero';
        } elseif ($count == 1) {
            $suffix = 'one';
        } elseif ($count == 2) {
            $suffix = 'two';
        } elseif ($count >= 3 && $count <= 10) {
            $suffix = 'few';
        } elseif ($count >= 11 && $count <= 99) {
            $suffix = 'many';
        } else {
            $suffix = 'other';
        }

        return __($key . '.' . $suffix, array_merge(['count' => $count], $replace));
    }
}

if (!function_exists('formatSudanesePhone')) {
    /**
     * تنسيق رقم الهاتف السوداني
     *
     * @param string $phone
     * @return string
     */
    function formatSudanesePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 9) {
            $phone = '249' . $phone;
        } elseif (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '249' . substr($phone, 1);
        }

        return '+' . $phone;
    }
}

if (!function_exists('generateOrderNumber')) {
    /**
     * إنشاء رقم طلب فريد
     *
     * @return string
     */
    function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }
}

if (!function_exists('generateTransactionId')) {
    /**
     * إنشاء رقم معاملة فريد
     *
     * @return string
     */
    function generateTransactionId()
    {
        return 'TXN-' . time() . '-' . strtoupper(Str::random(8));
    }
}

if (!function_exists('calculateDistance')) {
    /**
     * حساب المسافة بين نقطتين
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float المسافة بالكيلومتر
     */
    function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}

if (!function_exists('getDefaultCurrency')) {
    /**
     * الحصول على العملة الافتراضية
     *
     * @return Currency
     */
    function getDefaultCurrency()
    {
        return Currency::getDefault();
    }
}

if (!function_exists('getUserCurrency')) {
    /**
     * الحصول على عملة المستخدم
     *
     * @return Currency
     */
    function getUserCurrency()
    {
        if (auth()->check() && auth()->user()->preferred_currency) {
            return Currency::getByCode(auth()->user()->preferred_currency);
        }

        return getDefaultCurrency();
    }
}

if (!function_exists('sanitizeArabicText')) {
    /**
     * تنظيف النص العربي
     *
     * @param string $text
     * @return string
     */
    function sanitizeArabicText($text)
    {
        // إزالة التشكيل
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);

        // توحيد الهمزات
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace(['ة'], 'ه', $text);

        return trim($text);
    }
}

if (!function_exists('arabicNumbers')) {
    /**
     * تحويل الأرقام الإنجليزية إلى عربية
     *
     * @param string $text
     * @return string
     */
    function arabicNumbers($text)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($western, $eastern, $text);
    }
}

if (!function_exists('englishNumbers')) {
    /**
     * تحويل الأرقام العربية إلى إنجليزية
     *
     * @param string $text
     * @return string
     */
    function englishNumbers($text)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($eastern, $western, $text);
    }
}

if (!function_exists('timeAgo')) {
    /**
     * عرض الوقت منذ (منذ ساعة، منذ يومين، إلخ)
     *
     * @param string|\Carbon\Carbon $date
     * @return string
     */
    function timeAgo($date)
    {
        if (!$date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->diffForHumans();
    }
}

if (!function_exists('asset_cdn')) {
    /**
     * الحصول على رابط CDN للملف
     *
     * @param string $path
     * @return string
     */
    function asset_cdn($path)
    {
        $cdnUrl = env('CDN_URL');

        if ($cdnUrl) {
            return rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');
        }

        return asset($path);
    }
}

if (!function_exists('isProduction')) {
    /**
     * التحقق من بيئة الإنتاج
     *
     * @return bool
     */
    function isProduction()
    {
        return app()->environment('production');
    }
}

if (!function_calls('platformCommission')) {
    /**
     * حساب عمولة المنصة
     *
     * @param float $amount
     * @param float $rate
     * @return float
     */
    function platformCommission($amount, $rate = null)
    {
        $rate = $rate ?? config('app.platform_commission_rate', 0.05);
        return $amount * $rate;
    }
}
