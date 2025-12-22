# StoreWallet - منصة التجارة الإلكترونية السودانية

<div dir="rtl">

## نظرة عامة

**StoreWallet** هي منصة تجارة إلكترونية شاملة مصممة خصيصاً للسوق السوداني، مع دعم كامل للغة العربية ومتطلبات السوق المحلي. المنصة تدمج نظام المحفظة الإلكترونية xCash v3.0 مع سوق متعدد البائعين، نظام الضمان (Escrow)، المزادات، وخدمات الشحن.

</div>

## Overview

**StoreWallet** is a comprehensive e-commerce platform designed specifically for the Sudanese market, with full Arabic language support and local market requirements. The platform integrates the xCash v3.0 electronic wallet with a multi-vendor marketplace, escrow system, auctions, and shipping services.

---

<div dir="rtl">

## المميزات الرئيسية

### 🛍️ السوق الإلكتروني
- سوق متعدد البائعين مع لوحات تحكم منفصلة
- نظام الضمان (Escrow) لحماية المشتري والبائع
- دعم كامل للمنتجات المتنوعة مع فئات متعددة المستويات
- نظام تقييمات ومراجعات شامل
- إدارة المخزون والمتغيرات (الحجم، اللون، إلخ)

### 💰 المحفظة الإلكترونية
- تكامل كامل مع xCash v3.0
- عمليات الإيداع والسحب الفورية
- سجل معاملات مفصل
- دعم تحويلات P2P (من شخص لشخص)
- رصيد الضمان المنفصل

### 🎯 نظام المزادات (3 أنواع)
1. **مزادات المنتجات**: مزايدة تقليدية - أعلى عرض يفوز
2. **مزادات التوصيل**: مزايدة عكسية - أقل عرض يفوز (للتوصيل المحلي)
3. **مزادات الشحن الدولي**: مزايدة عكسية - أقل عرض يفوز (للشحن الدولي)

### 🚚 نظام شركات الشحن
- تسجيل شركات الشحن مع لوحة تحكم مخصصة
- تتبع GPS للشحنات في الوقت الفعلي
- حالات متعددة للشحنات (قيد الانتظار، جاري التوصيل، مكتمل)
- حساب تلقائي للمسافة والتكلفة
- تصنيفات وتقييمات شركات الشحن

### 💸 التحويلات المالية (Remittance)
- إرسال الأموال محلياً ودولياً
- استلام نقدي أو تحويل للمحفظة/البنك
- دعم كامل للمغتربين السودانيين
- أكثر من 10 عملات مدعومة
- رموز استلام آمنة مع OTP

### 🏙️ التخصيص للسوق السوداني
- **70+ مدينة سودانية** عبر 18 ولاية
- دعم جميع الولايات السودانية (الخرطوم، بحري، أم درمان، بورتسودان، إلخ)
- **10 عملات** مع الجنيه السوداني (SDG) كعملة افتراضية
- التوقيت المحلي (Africa/Khartoum - CAT UTC+2)
- تنسيق أرقام الهواتف السودانية (+249)

### 🌍 اللغات والعملات المدعومة
**اللغات:**
- العربية (اللغة الأساسية مع دعم RTL كامل)
- الإنجليزية

**العملات (10):**
- SDG (الجنيه السوداني) - العملة الافتراضية
- USD (الدولار الأمريكي)
- EUR (اليورو)
- GBP (الجنيه الإسترليني)
- SAR (الريال السعودي)
- AED (الدرهم الإماراتي)
- EGP (الجنيه المصري)
- QAR (الريال القطري)
- KWD (الدينار الكويتي)
- OMR (الريال العماني)

### 📱 نظام الإشعارات
- إشعارات SMS عبر مزودي الخدمة السودانيين:
  - Sudatel
  - Zain Sudan
  - MTN Sudan
- إشعارات Push عبر Firebase Cloud Messaging (FCM)
- إشعارات البريد الإلكتروني
- قوالب جاهزة لجميع أنواع الإشعارات
- نظام OTP لتوثيق رقم الهاتف

### 💳 بوابات الدفع الإلكتروني
**بوابات سودانية:**
- Bankak (مع HMAC SHA256 للأمان)
- E15
- SudaniPay

**بوابات دولية:**
- Stripe (للمعاملات الدولية)

**المميزات:**
- معالجة دفع آمنة
- التحقق التلقائي من الدفعات
- نظام استرداد كامل
- دعم تعليق الأموال (Escrow Hold)

### 📊 لوحات التحكم والتقارير
- **لوحة تحكم رئيسية** بإحصائيات شاملة
- **تقارير المبيعات** (يومية، أسبوعية، شهرية، سنوية)
- **تقارير حسب المدينة والولاية**
- **قائمة أفضل التجار**
- **تحليلات المغتربين**
- **معدل التوصيل في الوقت المحدد**
- **إحصائيات المزادات والشحنات**
- **تقارير التحويلات المالية**
- التخزين المؤقت الذكي (5 دقائق) لتحسين الأداء

### 🔒 الأمان والحماية
- **تشفير AES-256-CBC** للبيانات الحساسة
- **تحديد معدل الطلبات (Rate Limiting)**:
  - API: 60 طلب/دقيقة
  - تسجيل الدخول: 5 محاولات/دقيقة
  - الدفع: 10 طلبات/دقيقة
- **المصادقة الثنائية (2FA)** إلزامية للمدراء والتجار
- حماية من CSRF، XSS، SQL Injection
- **سياسات كلمة المرور القوية**:
  - 8 أحرف كحد أدنى
  - أحرف كبيرة وصغيرة مطلوبة
  - انتهاء صلاحية كل 90 يوم
- **إجبار HTTPS** مع HSTS
- **رؤوس الأمان** (X-Frame-Options، CSP، إلخ)
- **نسخ احتياطية تلقائية** (يومية - تُحفظ لمدة 30 يوم)

### 🛠️ دوال مساعدة (25+ Helper Functions)
**إدارة الأموال والعملات:**
- `formatMoney()` - تنسيق الأموال مع رمز العملة
- `convertCurrency()` - تحويل العملات
- `platformCommission()` - حساب عمولة المنصة

**الإشعارات:**
- `sendSMS()` - إرسال رسائل SMS
- `sendNotification()` - إرسال إشعارات متعددة القنوات

**الموقع والمسافة:**
- `getCityName()` - الحصول على اسم المدينة
- `formatSudanesePhone()` - تنسيق أرقام الهواتف السودانية
- `calculateDistance()` - حساب المسافة بين موقعين

**اللغة والترجمة:**
- `isArabic()` - التحقق من اللغة العربية النشطة
- `rtl()` - التحقق من اتجاه RTL
- `arabicNumbers()` - تحويل الأرقام للعربية (123 → ١٢٣)
- `englishNumbers()` - تحويل الأرقام للإنجليزية (١٢٣ → 123)
- `sanitizeArabicText()` - تنظيف النصوص العربية

**عامة:**
- `generateOrderNumber()` - توليد رقم طلب فريد
- `generateTransactionId()` - توليد رقم معاملة فريد
- `timeAgo()` - عرض الوقت بشكل نسبي (منذ ساعة، منذ يومين)
- `asset_cdn()` - روابط CDN للملفات الثابتة

</div>

---

## Key Features

### 🛍️ Marketplace
- Multi-vendor marketplace with separate dashboards
- Escrow system for buyer/seller protection
- Full product support with multi-level categories
- Comprehensive ratings and reviews system
- Inventory and variants management (size, color, etc.)

### 💰 Electronic Wallet
- Full integration with xCash v3.0
- Instant deposit and withdrawal operations
- Detailed transaction history
- P2P transfer support
- Separate escrow balance

### 🎯 Auction System (3 Types)
1. **Product Auctions**: Traditional bidding - highest bid wins
2. **Delivery Auctions**: Reverse bidding - lowest bid wins (local delivery)
3. **International Shipping Auctions**: Reverse bidding - lowest bid wins (international shipping)

### 🚚 Shipping Companies System
- Shipping company registration with dedicated dashboard
- Real-time GPS tracking for shipments
- Multiple shipment statuses (pending, in transit, completed)
- Automatic distance and cost calculation
- Shipping company ratings and reviews

### 💸 Money Remittance
- Send money locally and internationally
- Cash pickup or wallet/bank transfer
- Full support for Sudanese expatriates
- 10+ supported currencies
- Secure pickup codes with OTP

### 🏙️ Sudanese Market Customization
- **70+ Sudanese cities** across 18 states
- Support for all Sudanese states (Khartoum, Bahri, Omdurman, Port Sudan, etc.)
- **10 currencies** with Sudanese Pound (SDG) as default
- Local timezone (Africa/Khartoum - CAT UTC+2)
- Sudanese phone number formatting (+249)

### 🌍 Supported Languages & Currencies
**Languages:**
- Arabic (primary language with full RTL support)
- English

**Currencies (10):**
- SDG (Sudanese Pound) - Default
- USD, EUR, GBP, SAR, AED, EGP, QAR, KWD, OMR

### 📱 Notification System
- SMS notifications via Sudanese providers:
  - Sudatel
  - Zain Sudan
  - MTN Sudan
- Push notifications via Firebase Cloud Messaging (FCM)
- Email notifications
- Ready templates for all notification types
- OTP system for phone verification

### 💳 Payment Gateways
**Sudanese Gateways:**
- Bankak (with HMAC SHA256 security)
- E15
- SudaniPay

**International Gateways:**
- Stripe (for international transactions)

**Features:**
- Secure payment processing
- Automatic payment verification
- Full refund system
- Escrow hold support

### 📊 Dashboards & Reports
- **Main Dashboard** with comprehensive statistics
- **Sales Reports** (daily, weekly, monthly, yearly)
- **Reports by City and State**
- **Top Merchants List**
- **Expatriate Analytics**
- **On-time Delivery Rate**
- **Auction and Shipment Statistics**
- **Remittance Reports**
- Smart caching (5 minutes) for performance

### 🔒 Security & Protection
- **AES-256-CBC encryption** for sensitive data
- **Rate Limiting**:
  - API: 60 requests/minute
  - Login: 5 attempts/minute
  - Payment: 10 requests/minute
- **Two-Factor Authentication (2FA)** required for admins and merchants
- CSRF, XSS, SQL Injection protection
- **Strong Password Policies**:
  - 8 characters minimum
  - Uppercase and lowercase required
  - Expiration every 90 days
- **Force HTTPS** with HSTS
- **Security Headers** (X-Frame-Options, CSP, etc.)
- **Automatic Backups** (daily - kept for 30 days)

### 🛠️ Helper Functions (25+)
**Money & Currency:**
- `formatMoney()`, `convertCurrency()`, `platformCommission()`

**Notifications:**
- `sendSMS()`, `sendNotification()`

**Location & Distance:**
- `getCityName()`, `formatSudanesePhone()`, `calculateDistance()`

**Language & Translation:**
- `isArabic()`, `rtl()`, `arabicNumbers()`, `englishNumbers()`

**General:**
- `generateOrderNumber()`, `generateTransactionId()`, `timeAgo()`, `asset_cdn()`

---

<div dir="rtl">

## هيكل قاعدة البيانات

المنصة تستخدم **قاعدتي بيانات منفصلتين**:
1. **storewallet** - قاعدة بيانات السوق الرئيسية
2. **xcash** - قاعدة بيانات المحفظة الإلكترونية

### الجداول الرئيسية (19 جدول)

**المستخدمون والمصادقة:**
- `users` - بيانات المستخدمين
- `user_verifications` - توثيق الهوية والوثائق
- `sessions` - إدارة الجلسات

**المنتجات والمتاجر:**
- `merchants` - بيانات التجار
- `products` - المنتجات
- `product_images` - صور المنتجات
- `product_variants` - متغيرات المنتجات
- `categories` - الفئات

**الطلبات والمعاملات:**
- `orders` - الطلبات
- `order_items` - عناصر الطلب
- `escrow_transactions` - معاملات الضمان
- `reviews` - التقييمات والمراجعات

**المزادات:**
- `auctions` - المزادات (3 أنواع)
- `auction_bids` - عروض المزايدة

**الشحن:**
- `shipping_companies` - شركات الشحن
- `shipments` - الشحنات

**التحويلات المالية:**
- `remittances` - التحويلات المالية

**المواقع:**
- `sudanese_cities` - المدن السودانية (70+)
- `currencies` - العملات المدعومة (10)

</div>

## Database Structure

The platform uses **two separate databases**:
1. **storewallet** - Main marketplace database
2. **xcash** - Electronic wallet database

### Main Tables (19 tables)

**Users & Authentication:**
- `users`, `user_verifications`, `sessions`

**Products & Stores:**
- `merchants`, `products`, `product_images`, `product_variants`, `categories`

**Orders & Transactions:**
- `orders`, `order_items`, `escrow_transactions`, `reviews`

**Auctions:**
- `auctions`, `auction_bids`

**Shipping:**
- `shipping_companies`, `shipments`

**Remittances:**
- `remittances`

**Locations:**
- `sudanese_cities` (70+), `currencies` (10)

---

<div dir="rtl">

## التثبيت والإعداد

### المتطلبات
- PHP >= 7.3 (يُفضل PHP 8.0+)
- MySQL 5.7+
- Redis (للتخزين المؤقت والطوابير)
- Composer
- Node.js & NPM (للأصول الأمامية)

### خطوات التثبيت

1. **استنساخ المشروع:**
```bash
git clone https://github.com/alfainternational/storewallet.git
cd storewallet/laravel_application
```

2. **تثبيت الحزم:**
```bash
composer install
npm install
```

3. **إعداد ملف البيئة:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **تعديل ملف .env:**
   - إعدادات قاعدة البيانات (قاعدتين: storewallet و xcash)
   - مفاتيح SMS API (Sudatel, Zain, MTN)
   - بيانات بوابات الدفع (Bankak, E15, SudaniPay, Stripe)
   - إعدادات Firebase للإشعارات الفورية
   - إعدادات الأمان والنسخ الاحتياطي

5. **تشغيل الهجرات:**
```bash
# قاعدة بيانات السوق
php artisan migrate --database=mysql

# قاعدة بيانات المحفظة (إذا لزم الأمر)
php artisan migrate --database=wallet_db
```

6. **تعبئة البيانات الأولية:**
```bash
php artisan db:seed
```

7. **بناء الأصول:**
```bash
npm run production
```

8. **إنشاء رابط المخزن:**
```bash
php artisan storage:link
```

9. **ضبط الصلاحيات:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

10. **تشغيل الخادم:**
```bash
# للتطوير
php artisan serve

# للإنتاج - استخدم Nginx/Apache
```

</div>

## Installation & Setup

### Requirements
- PHP >= 7.3 (PHP 8.0+ preferred)
- MySQL 5.7+
- Redis (for cache and queues)
- Composer
- Node.js & NPM (for frontend assets)

### Installation Steps

1. **Clone the project:**
```bash
git clone https://github.com/alfainternational/storewallet.git
cd storewallet/laravel_application
```

2. **Install packages:**
```bash
composer install
npm install
```

3. **Setup environment file:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Edit .env file:**
   - Database settings (two databases: storewallet & xcash)
   - SMS API keys (Sudatel, Zain, MTN)
   - Payment gateway credentials (Bankak, E15, SudaniPay, Stripe)
   - Firebase settings for push notifications
   - Security and backup settings

5. **Run migrations:**
```bash
# Marketplace database
php artisan migrate --database=mysql

# Wallet database (if needed)
php artisan migrate --database=wallet_db
```

6. **Seed initial data:**
```bash
php artisan db:seed
```

7. **Build assets:**
```bash
npm run production
```

8. **Create storage link:**
```bash
php artisan storage:link
```

9. **Set permissions:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

10. **Run server:**
```bash
# For development
php artisan serve

# For production - use Nginx/Apache
```

---

<div dir="rtl">

## الإعدادات الهامة

### 1. إعدادات SMS (مزودو الخدمة السودانيين)
في ملف `.env`:
```env
SMS_PROVIDER=sudatel
SUDATEL_API_KEY=your_api_key
SUDATEL_SENDER_ID=your_sender_id
ZAIN_API_KEY=your_api_key
MTN_API_KEY=your_api_key
```

### 2. إعدادات بوابات الدفع
```env
# Bankak
BANKAK_MERCHANT_ID=your_merchant_id
BANKAK_API_KEY=your_api_key
BANKAK_API_SECRET=your_api_secret

# E15
E15_API_KEY=your_api_key
E15_MERCHANT_ID=your_merchant_id

# Stripe
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### 3. إعدادات Firebase (الإشعارات الفورية)
```env
FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_sender_id
```

### 4. إعدادات الأمان
```env
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
RATE_LIMIT_API=60
RATE_LIMIT_LOGIN=5
PASSWORD_EXPIRE_DAYS=90
```

### 5. إعدادات النسخ الاحتياطي
```env
BACKUP_ENABLED=true
BACKUP_SCHEDULE=daily
BACKUP_RETENTION_DAYS=30
BACKUP_DISK=s3
```

</div>

## Important Settings

### 1. SMS Settings (Sudanese Providers)
In `.env` file:
```env
SMS_PROVIDER=sudatel
SUDATEL_API_KEY=your_api_key
SUDATEL_SENDER_ID=your_sender_id
ZAIN_API_KEY=your_api_key
MTN_API_KEY=your_api_key
```

### 2. Payment Gateway Settings
```env
# Bankak
BANKAK_MERCHANT_ID=your_merchant_id
BANKAK_API_KEY=your_api_key
BANKAK_API_SECRET=your_api_secret

# E15
E15_API_KEY=your_api_key
E15_MERCHANT_ID=your_merchant_id

# Stripe
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### 3. Firebase Settings (Push Notifications)
```env
FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_sender_id
```

### 4. Security Settings
```env
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
RATE_LIMIT_API=60
RATE_LIMIT_LOGIN=5
PASSWORD_EXPIRE_DAYS=90
```

### 5. Backup Settings
```env
BACKUP_ENABLED=true
BACKUP_SCHEDULE=daily
BACKUP_RETENTION_DAYS=30
BACKUP_DISK=s3
```

---

<div dir="rtl">

## المدن السودانية المدعومة (70+)

### ولاية الخرطوم
الخرطوم، أم درمان، الخرطوم بحري، شرق النيل

### ولاية البحر الأحمر
بورتسودان، سواكن، طوكر، سنكات، حلايب

### ولاية كسلا
كسلا، ود الحليو، خشم القربة

### ولاية القضارف
القضارف، الفاو، الحواتة

### ولاية نهر النيل
عطبرة، الدامر، شندي، المتمة، بربر

### ولاية الشمالية
دنقلا، كريمة، مروي، وادي حلفا

### ولاية الجزيرة
ود مدني، الحصاحيصا، المناقل، الكاملين

### ولاية النيل الأبيض
ربك، كوستي، الدويم

### ولاية سنار
سنار، سنجة

### ولاية النيل الأزرق
الدمازين، الروصيرص، الكرمك

### ولاية شمال كردفان
الأبيض، بارا، سودري

### ولاية جنوب كردفان
كادقلي، الدلنج

### ولايات دارفور
- **شمال دارفور:** الفاشر، كبكابية، الطينة، الكُمّا
- **جنوب دارفور:** نيالا، كاس، الضعين
- **غرب دارفور:** الجنينة، قرضة
- **شرق دارفور:** أبو جابرة
- **وسط دارفور:** زالنجي، أم دخن

### ولاية غرب كردفان
الفولة، بابنوسة

</div>

## Supported Sudanese Cities (70+)

Cities across all 18 Sudanese states including:
- Khartoum State: Khartoum, Omdurman, Khartoum North, Sharq an Neel
- Red Sea State: Port Sudan, Suakin, Tokar, Sinkat, Halaib
- And all other states as listed in the Arabic section above

---

<div dir="rtl">

## الاستخدام

### واجهات API الرئيسية

**المصادقة:**
- `POST /api/register` - التسجيل
- `POST /api/login` - تسجيل الدخول
- `POST /api/verify-phone` - توثيق الهاتف
- `POST /api/logout` - تسجيل الخروج

**المنتجات:**
- `GET /api/products` - قائمة المنتجات
- `GET /api/products/{id}` - تفاصيل منتج
- `POST /api/products` - إضافة منتج (للتجار)
- `PUT /api/products/{id}` - تعديل منتج

**الطلبات:**
- `POST /api/orders` - إنشاء طلب
- `GET /api/orders` - طلباتي
- `GET /api/orders/{id}` - تفاصيل طلب
- `POST /api/orders/{id}/review` - إضافة تقييم

**المزادات:**
- `GET /api/auctions` - قائمة المزادات
- `POST /api/auctions` - إنشاء مزاد
- `POST /api/auctions/{id}/bid` - تقديم عرض

**التحويلات:**
- `POST /api/remittances` - إنشاء تحويل
- `GET /api/remittances` - تحويلاتي
- `POST /api/remittances/{id}/verify` - توثيق استلام

**المحفظة:**
- `GET /api/wallet/balance` - الرصيد
- `POST /api/wallet/deposit` - إيداع
- `POST /api/wallet/withdraw` - سحب
- `GET /api/wallet/transactions` - المعاملات

</div>

## Usage

### Main API Endpoints

See Arabic section above for complete API endpoints list.

---

<div dir="rtl">

## المساهمة

نرحب بمساهماتكم! يرجى:
1. عمل Fork للمشروع
2. إنشاء فرع للميزة الجديدة (`git checkout -b feature/amazing-feature`)
3. Commit للتغييرات (`git commit -m 'Add amazing feature'`)
4. Push للفرع (`git push origin feature/amazing-feature`)
5. فتح Pull Request

</div>

## Contributing

We welcome contributions! Please:
1. Fork the project
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

<div dir="rtl">

## الترخيص

هذا المشروع مرخص بموجب رخصة MIT.

## الدعم والاتصال

- **البريد الإلكتروني:** support@storewallet.sd
- **الموقع:** https://storewallet.sd
- **الدعم الفني:** https://storewallet.sd/support

</div>

## License

This project is licensed under the MIT License.

## Support & Contact

- **Email:** support@storewallet.sd
- **Website:** https://storewallet.sd
- **Technical Support:** https://storewallet.sd/support

---

<div dir="rtl">

## تقنيات المشروع

- **Backend:** Laravel 8.x/9.x (PHP 8.0+)
- **Database:** MySQL 5.7+, Redis
- **Frontend:** Vue.js / Blade Templates
- **Mobile:** Native/Hybrid (قيد التطوير)
- **الإشعارات:** Firebase FCM, SMS APIs
- **الدفع:** Bankak, E15, SudaniPay, Stripe
- **الأمان:** AES-256-CBC, 2FA, Rate Limiting
- **الاستضافة:** AWS/DigitalOcean recommended

</div>

## Technologies

- **Backend:** Laravel 8.x/9.x (PHP 8.0+)
- **Database:** MySQL 5.7+, Redis
- **Frontend:** Vue.js / Blade Templates
- **Mobile:** Native/Hybrid (in development)
- **Notifications:** Firebase FCM, SMS APIs
- **Payment:** Bankak, E15, SudaniPay, Stripe
- **Security:** AES-256-CBC, 2FA, Rate Limiting
- **Hosting:** AWS/DigitalOcean recommended

---

<div dir="rtl" style="text-align: center; margin-top: 40px;">

**صُنع بـ ❤️ للسوق السوداني**

**Made with ❤️ for the Sudanese Market**

</div>
