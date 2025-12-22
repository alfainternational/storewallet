<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * خدمة بوابات الدفع الإلكتروني
 * تدعم بوابات الدفع السودانية والدولية
 */
class PaymentGatewayService
{
    protected $gateway;

    /**
     * تهيئة بوابة الدفع
     */
    public function __construct($gateway = null)
    {
        $this->gateway = $gateway ?? config('services.payment.default_gateway', 'bankak');
    }

    /**
     * إنشاء عملية دفع جديدة
     *
     * @param array $paymentData
     * @return array
     */
    public function createPayment($paymentData)
    {
        try {
            switch ($this->gateway) {
                case 'bankak':
                    return $this->createBankakPayment($paymentData);

                case 'e15':
                    return $this->createE15Payment($paymentData);

                case 'sudanipay':
                    return $this->createSudaniPayPayment($paymentData);

                case 'stripe':
                    return $this->createStripePayment($paymentData);

                default:
                    throw new Exception('Unsupported payment gateway');
            }
        } catch (Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'فشل إنشاء عملية الدفع',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Bankak Payment Gateway (بوابة بنكك السودانية)
     */
    protected function createBankakPayment($data)
    {
        $apiKey = env('BANKAK_API_KEY');
        $merchantId = env('BANKAK_MERCHANT_ID');
        $apiSecret = env('BANKAK_API_SECRET');

        if (!$apiKey || !$merchantId) {
            throw new Exception('Bankak credentials not configured');
        }

        $endpoint = env('BANKAK_API_URL', 'https://api.bankak.sd/v1/payment');

        $payload = [
            'merchant_id' => $merchantId,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'SDG',
            'order_id' => $data['order_id'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'callback_url' => route('payment.callback'),
            'return_url' => $data['return_url'],
            'description' => $data['description'] ?? 'StoreWallet Payment',
        ];

        // إنشاء التوقيع الآمن
        $signature = $this->generateBankakSignature($payload, $apiSecret);
        $payload['signature'] = $signature;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            $result = $response->json();

            return [
                'success' => true,
                'payment_id' => $result['payment_id'],
                'payment_url' => $result['payment_url'],
                'transaction_id' => $result['transaction_id'],
                'expires_at' => $result['expires_at'] ?? null,
            ];
        }

        throw new Exception('Bankak payment failed: ' . $response->body());
    }

    /**
     * E15 Payment Gateway (بوابة E15)
     */
    protected function createE15Payment($data)
    {
        $apiKey = env('E15_API_KEY');
        $merchantCode = env('E15_MERCHANT_CODE');

        if (!$apiKey || !$merchantCode) {
            throw new Exception('E15 credentials not configured');
        }

        $endpoint = 'https://api.e15.sd/payment/create';

        $payload = [
            'merchant_code' => $merchantCode,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'SDG',
            'reference' => $data['order_id'],
            'customer' => [
                'name' => $data['customer_name'],
                'email' => $data['customer_email'],
                'phone' => $data['customer_phone'],
            ],
            'callback_url' => route('payment.callback'),
            'success_url' => $data['return_url'],
            'cancel_url' => $data['cancel_url'] ?? $data['return_url'],
        ];

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            $result = $response->json();

            return [
                'success' => true,
                'payment_id' => $result['payment_id'],
                'payment_url' => $result['checkout_url'],
                'transaction_id' => $result['reference'],
            ];
        }

        throw new Exception('E15 payment failed: ' . $response->body());
    }

    /**
     * SudaniPay Gateway
     */
    protected function createSudaniPayPayment($data)
    {
        // TODO: تطبيق SudaniPay API
        Log::info('SudaniPay payment created', $data);

        return [
            'success' => true,
            'payment_id' => 'SP_' . uniqid(),
            'payment_url' => 'https://checkout.sudanipay.sd/' . uniqid(),
            'transaction_id' => 'TXN_' . time(),
        ];
    }

    /**
     * Stripe Payment Gateway (للمدفوعات الدولية)
     */
    protected function createStripePayment($data)
    {
        $stripeKey = env('STRIPE_SECRET_KEY');

        if (!$stripeKey) {
            throw new Exception('Stripe credentials not configured');
        }

        // استخدام Stripe PHP SDK
        \Stripe\Stripe::setApiKey($stripeKey);

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($data['currency'] ?? 'usd'),
                        'product_data' => [
                            'name' => $data['description'] ?? 'StoreWallet Payment',
                        ],
                        'unit_amount' => $data['amount'] * 100, // Stripe uses cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['return_url'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $data['cancel_url'] ?? $data['return_url'],
                'customer_email' => $data['customer_email'],
                'metadata' => [
                    'order_id' => $data['order_id'],
                ],
            ]);

            return [
                'success' => true,
                'payment_id' => $session->id,
                'payment_url' => $session->url,
                'transaction_id' => $session->payment_intent,
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception('Stripe payment failed: ' . $e->getMessage());
        }
    }

    /**
     * التحقق من حالة الدفع
     */
    public function verifyPayment($paymentId)
    {
        try {
            switch ($this->gateway) {
                case 'bankak':
                    return $this->verifyBankakPayment($paymentId);

                case 'e15':
                    return $this->verifyE15Payment($paymentId);

                case 'sudanipay':
                    return $this->verifySudaniPayPayment($paymentId);

                case 'stripe':
                    return $this->verifyStripePayment($paymentId);

                default:
                    throw new Exception('Unsupported payment gateway');
            }
        } catch (Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * التحقق من دفعة Bankak
     */
    protected function verifyBankakPayment($paymentId)
    {
        $apiKey = env('BANKAK_API_KEY');
        $endpoint = env('BANKAK_API_URL', 'https://api.bankak.sd/v1/payment') . '/' . $paymentId;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($endpoint);

        if ($response->successful()) {
            $result = $response->json();

            return [
                'success' => true,
                'status' => $result['status'], // pending, paid, failed, cancelled
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'paid_at' => $result['paid_at'] ?? null,
                'transaction_id' => $result['transaction_id'],
            ];
        }

        throw new Exception('Bankak verification failed');
    }

    /**
     * التحقق من دفعة E15
     */
    protected function verifyE15Payment($paymentId)
    {
        $apiKey = env('E15_API_KEY');
        $endpoint = 'https://api.e15.sd/payment/' . $paymentId;

        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
        ])->get($endpoint);

        if ($response->successful()) {
            $result = $response->json();

            return [
                'success' => true,
                'status' => $result['status'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'paid_at' => $result['completed_at'] ?? null,
                'transaction_id' => $result['reference'],
            ];
        }

        throw new Exception('E15 verification failed');
    }

    /**
     * التحقق من دفعة SudaniPay
     */
    protected function verifySudaniPayPayment($paymentId)
    {
        // TODO: تطبيق SudaniPay verification
        return [
            'success' => true,
            'status' => 'paid',
            'amount' => 0,
            'currency' => 'SDG',
        ];
    }

    /**
     * التحقق من دفعة Stripe
     */
    protected function verifyStripePayment($sessionId)
    {
        $stripeKey = env('STRIPE_SECRET_KEY');
        \Stripe\Stripe::setApiKey($stripeKey);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            return [
                'success' => true,
                'status' => $session->payment_status, // paid, unpaid, no_payment_required
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'paid_at' => $session->payment_status === 'paid' ? now() : null,
                'transaction_id' => $session->payment_intent,
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception('Stripe verification failed: ' . $e->getMessage());
        }
    }

    /**
     * استرداد المبلغ
     */
    public function refundPayment($paymentId, $amount = null)
    {
        try {
            switch ($this->gateway) {
                case 'bankak':
                    return $this->refundBankakPayment($paymentId, $amount);

                case 'stripe':
                    return $this->refundStripePayment($paymentId, $amount);

                default:
                    throw new Exception('Refund not supported for this gateway');
            }
        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * استرداد دفعة Bankak
     */
    protected function refundBankakPayment($paymentId, $amount)
    {
        $apiKey = env('BANKAK_API_KEY');
        $endpoint = env('BANKAK_API_URL', 'https://api.bankak.sd/v1/payment') . '/' . $paymentId . '/refund';

        $payload = ['amount' => $amount];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'refund_id' => $response->json()['refund_id'],
            ];
        }

        throw new Exception('Bankak refund failed');
    }

    /**
     * استرداد دفعة Stripe
     */
    protected function refundStripePayment($paymentIntentId, $amount)
    {
        $stripeKey = env('STRIPE_SECRET_KEY');
        \Stripe\Stripe::setApiKey($stripeKey);

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount ? $amount * 100 : null,
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception('Stripe refund failed: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء توقيع Bankak
     */
    protected function generateBankakSignature($data, $secret)
    {
        ksort($data);
        $string = '';
        foreach ($data as $key => $value) {
            if ($key !== 'signature') {
                $string .= $key . '=' . $value . '&';
            }
        }
        $string = rtrim($string, '&');
        return hash_hmac('sha256', $string, $secret);
    }
}
