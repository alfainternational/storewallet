<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    | إعدادات الأمان للمنصة
    */

    // Rate Limiting
    'rate_limit' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        'api' => env('API_RATE_LIMIT', 60), // requests per minute
        'login' => 5, // login attempts per minute
        'register' => 3, // registrations per hour
        'otp' => 3, // OTP requests per hour
        'payment' => 10, // payment attempts per hour
    ],

    // Session Security
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'expire_on_close' => true,
        'secure' => env('SESSION_SECURE_COOKIE', true), // HTTPS only
        'http_only' => true,
        'same_site' => 'lax',
        'max_devices' => 3, // Maximum concurrent sessions per user
    ],

    // Password Policy
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => false,
        'expire_days' => 90, // Force password change after 90 days
        'remember_previous' => 5, // Don't allow reusing last 5 passwords
    ],

    // Two-Factor Authentication
    '2fa' => [
        'enabled' => env('2FA_ENABLED', false),
        'required_for' => ['admin', 'merchant'], // Required for these user types
        'methods' => ['sms', 'email', 'app'],
        'code_length' => 6,
        'code_expiry' => 10, // minutes
    ],

    // OTP Configuration
    'otp' => [
        'length' => 6,
        'expiry' => 10, // minutes
        'max_attempts' => 3,
        'resend_cooldown' => 60, // seconds
    ],

    // IP Whitelisting
    'ip_whitelist' => [
        'enabled' => env('IP_WHITELIST_ENABLED', false),
        'admin_ips' => explode(',', env('ADMIN_WHITELIST_IPS', '')),
    ],

    // CORS Configuration
    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ],

    // File Upload Security
    'file_upload' => [
        'max_size' => env('MAX_FILE_SIZE', 10240), // KB
        'max_image_size' => env('MAX_IMAGE_SIZE', 5120), // KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'scan_uploads' => env('SCAN_UPLOADS', true),
    ],

    // SQL Injection Protection
    'sql_protection' => [
        'enabled' => true,
        'log_attempts' => true,
        'block_suspicious_queries' => true,
    ],

    // XSS Protection
    'xss_protection' => [
        'enabled' => true,
        'sanitize_inputs' => true,
        'escape_outputs' => true,
    ],

    // CSRF Protection
    'csrf' => [
        'enabled' => true,
        'expire' => 120, // minutes
        'regenerate' => true, // Regenerate token after each request
    ],

    // API Security
    'api' => [
        'auth_methods' => ['bearer', 'api_key'],
        'require_https' => env('API_REQUIRE_HTTPS', true),
        'api_key_length' => 32,
        'api_key_expiry' => 365, // days
    ],

    // Audit Logging
    'audit' => [
        'enabled' => true,
        'log_all_requests' => false,
        'log_failed_logins' => true,
        'log_suspicious_activity' => true,
        'retention_days' => 90,
    ],

    // Encryption
    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'sensitive_fields' => [
            'password',
            'pin',
            'otp',
            'api_key',
            'api_secret',
            'bank_account_number',
            'national_id',
        ],
    ],

    // Suspicious Activity Detection
    'suspicious_activity' => [
        'enabled' => true,
        'multiple_failed_logins' => 5,
        'multiple_payment_attempts' => 3,
        'unusual_location' => true,
        'vpn_detection' => env('VPN_DETECTION_ENABLED', false),
    ],

    // Backup & Recovery
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'schedule' => env('BACKUP_SCHEDULE', 'daily'),
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'encrypt_backups' => true,
        'remote_backup' => env('REMOTE_BACKUP_ENABLED', true),
    ],

    // SSL/TLS
    'ssl' => [
        'force_https' => env('FORCE_HTTPS', true),
        'hsts_enabled' => true,
        'hsts_max_age' => 31536000, // 1 year
    ],

    // Content Security Policy
    'csp' => [
        'enabled' => true,
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-inline'"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'https:'],
        'font-src' => ["'self'", 'data:'],
    ],

    // Security Headers
    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

];
