<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * خدمة الإشعارات الشاملة
 * تدعم SMS و Push Notifications والبريد الإلكتروني
 */
class NotificationService
{
    /**
     * إرسال رسالة SMS
     *
     * @param string $phone رقم الهاتف
     * @param string $message الرسالة
     * @return bool
     */
    public function sendSMS($phone, $message)
    {
        try {
            // تنظيف رقم الهاتف
            $phone = $this->formatSudanesePhone($phone);

            // اختيار مزود الخدمة حسب الإعدادات
            $provider = config('services.sms.provider', 'sudatel');

            switch ($provider) {
                case 'sudatel':
                    return $this->sendViaSudatel($phone, $message);

                case 'zain':
                    return $this->sendViaZain($phone, $message);

                case 'mtn':
                    return $this->sendViaMTN($phone, $message);

                default:
                    return $this->sendViaSudatel($phone, $message);
            }

        } catch (Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال عبر سوداتل
     */
    protected function sendViaSudatel($phone, $message)
    {
        $apiKey = env('SMS_API_KEY');
        $apiSecret = env('SMS_API_SECRET');
        $senderId = env('SMS_SENDER_ID', 'StoreWallet');

        if (!$apiKey || !$apiSecret) {
            Log::warning('SMS credentials not configured');
            return false;
        }

        // API endpoint لسوداتل (مثال - يجب تحديثه بالـ endpoint الصحيح)
        $endpoint = 'https://api.sudatel.sd/sms/send';

        try {
            $response = Http::post($endpoint, [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'sender_id' => $senderId,
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$phone}");
                return true;
            }

            Log::error("SMS failed: " . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error("SMS exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال عبر زين
     */
    protected function sendViaZain($phone, $message)
    {
        // TODO: تطبيق API زين السودان
        Log::info("Zain SMS to {$phone}: {$message}");
        return true;
    }

    /**
     * إرسال عبر MTN
     */
    protected function sendViaMTN($phone, $message)
    {
        // TODO: تطبيق API MTN السودان
        Log::info("MTN SMS to {$phone}: {$message}");
        return true;
    }

    /**
     * تنسيق رقم الهاتف السوداني
     */
    protected function formatSudanesePhone($phone)
    {
        // إزالة المسافات والرموز
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // إضافة مفتاح السودان +249
        if (strlen($phone) === 9) {
            $phone = '249' . $phone;
        } elseif (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '249' . substr($phone, 1);
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) !== '249') {
            // Already has country code
        }

        return '+' . $phone;
    }

    /**
     * إرسال إشعار Push
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendPushNotification($userId, $title, $body, $data = [])
    {
        try {
            // استخدام FCM (Firebase Cloud Messaging)
            $fcmToken = $this->getUserFCMToken($userId);

            if (!$fcmToken) {
                Log::info("No FCM token for user {$userId}");
                return false;
            }

            $fcmServerKey = env('FCM_SERVER_KEY');

            if (!$fcmServerKey) {
                Log::warning('FCM Server Key not configured');
                return false;
            }

            $notification = [
                'title' => $title,
                'body' => $body,
                'icon' => asset('images/logo.png'),
                'click_action' => url('/'),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => $notification,
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                Log::info("Push notification sent to user {$userId}");
                return true;
            }

            Log::error("Push notification failed: " . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error("Push notification exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على FCM Token للمستخدم
     */
    protected function getUserFCMToken($userId)
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->fcm_token : null;
    }

    /**
     * إرسال إشعار كامل (SMS + Push + Email)
     *
     * @param int $userId
     * @param string $type نوع الإشعار
     * @param array $data البيانات
     * @return array
     */
    public function sendNotification($userId, $type, $data)
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $result = [
            'sms' => false,
            'push' => false,
            'email' => false,
        ];

        // الحصول على قالب الإشعار
        $template = $this->getNotificationTemplate($type, $data);

        // إرسال SMS إذا كان مفعل
        if ($user->sms_notifications && $user->phone) {
            $result['sms'] = $this->sendSMS($user->phone, $template['sms']);
        }

        // إرسال Push Notification
        if ($user->push_notifications) {
            $result['push'] = $this->sendPushNotification(
                $userId,
                $template['title'],
                $template['body'],
                $data
            );
        }

        // إرسال Email
        if ($user->email_notifications && $user->email) {
            $result['email'] = $this->sendEmail($user->email, $template);
        }

        return $result;
    }

    /**
     * الحصول على قالب الإشعار
     */
    protected function getNotificationTemplate($type, $data)
    {
        $templates = [
            'order_placed' => [
                'title' => __('common.order_placed'),
                'body' => __('marketplace.order_confirmed'),
                'sms' => "تم تأكيد طلبك رقم {$data['order_number']}",
            ],
            'order_shipped' => [
                'title' => __('marketplace.order_shipped'),
                'body' => __('marketplace.order_on_way'),
                'sms' => "طلبك رقم {$data['order_number']} في الطريق إليك",
            ],
            'payment_received' => [
                'title' => __('wallet.payment_received'),
                'body' => __('wallet.money_received'),
                'sms' => "تم استلام {$data['amount']} {$data['currency']} في محفظتك",
            ],
            'auction_won' => [
                'title' => __('auctions.auction_won'),
                'body' => __('auctions.you_won'),
                'sms' => "مبروك! فزت بالمزاد {$data['auction_title']}",
            ],
            'bid_outbid' => [
                'title' => __('auctions.outbid_notification'),
                'body' => __('auctions.outbid'),
                'sms' => "تم تجاوز عرضك في المزاد {$data['auction_title']}",
            ],
            'remittance_received' => [
                'title' => __('remittance.remittance_received'),
                'body' => __('remittance.money_received'),
                'sms' => "تم استلام تحويل مالي بقيمة {$data['amount']} {$data['currency']}",
            ],
        ];

        return $templates[$type] ?? [
            'title' => 'إشعار جديد',
            'body' => 'لديك إشعار جديد',
            'sms' => 'لديك إشعار جديد من StoreWallet',
        ];
    }

    /**
     * إرسال بريد إلكتروني
     */
    protected function sendEmail($email, $template)
    {
        try {
            // TODO: تطبيق إرسال البريد الإلكتروني
            Log::info("Email sent to {$email}: {$template['title']}");
            return true;
        } catch (Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال OTP عبر SMS
     */
    public function sendOTP($phone, $code)
    {
        $message = "رمز التحقق الخاص بك: {$code}\n";
        $message .= "صالح لمدة 10 دقائق\n";
        $message .= "StoreWallet";

        return $this->sendSMS($phone, $message);
    }

    /**
     * إرسال رمز استلام التحويل
     */
    public function sendPickupCode($phone, $code, $amount)
    {
        $message = "رمز الاستلام: {$code}\n";
        $message .= "المبلغ: {$amount}\n";
        $message .= "أظهر هذا الرمز للوكيل\n";
        $message .= "StoreWallet";

        return $this->sendSMS($phone, $message);
    }
}
