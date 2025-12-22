# StoreWallet Mobile App (Flutter)

تطبيق الموبايل لمنصة **StoreWallet** - منصة التجارة الإلكترونية السودانية

## المميزات

### 🎯 الوظائف الأساسية
- ✅ تسجيل الدخول والتسجيل
- ✅ تصفح المنتجات مع فلاتر متقدمة
- ✅ عرض تفاصيل المنتج
- ✅ سلة التسوق
- ✅ إتمام الطلب
- ✅ المحفظة الإلكترونية
- ✅ التحويلات المالية (Remittances)
- ✅ المزادات (3 أنواع)
- ✅ تتبع الطلبات
- ✅ الملف الشخصي

### 🌍 دعم اللغات
- ✅ العربية (اللغة الأساسية)
- ✅ الإنجليزية
- ✅ دعم RTL كامل للعربية

### 💳 بوابات الدفع
- ✅ Bankak (Sudanese)
- ✅ E15 (Sudanese)
- ✅ SudaniPay (Sudanese)
- ✅ Stripe (International)
- ✅ xCash Wallet Integration

### 📱 الإشعارات
- ✅ Firebase Cloud Messaging
- ✅ Local Notifications
- ✅ Push Notifications

### 🎨 التصميم
- ✅ Material Design 3
- ✅ Light & Dark Mode
- ✅ Responsive Layout
- ✅ Cairo Font للعربية
- ✅ Gradient UI Elements

## البنية التقنية

### Dependencies الرئيسية

```yaml
# State Management
provider: ^6.1.0
get: ^4.6.6

# Networking
http: ^1.1.0
dio: ^5.4.0

# Storage
shared_preferences: ^2.2.2
hive_flutter: ^1.1.0

# Localization
easy_localization: ^3.0.3

# Firebase
firebase_core: ^2.24.2
firebase_messaging: ^14.7.9

# UI Components
flutter_rating_bar: ^4.0.1
cached_network_image: ^3.3.0
shimmer: ^3.0.0

# Maps
google_maps_flutter: ^2.5.0
geolocator: ^10.1.0

# Payment
flutter_stripe: ^10.1.0

# Charts
fl_chart: ^0.65.0
```

## هيكل المشروع

```
lib/
├── main.dart                   # نقطة الدخول الرئيسية
├── models/                     # نماذج البيانات
│   ├── user.dart
│   ├── product.dart
│   ├── auction.dart
│   ├── order.dart
│   └── wallet.dart
├── providers/                  # State Management
│   ├── auth_provider.dart
│   ├── cart_provider.dart
│   ├── products_provider.dart
│   ├── auctions_provider.dart
│   ├── wallet_provider.dart
│   └── theme_provider.dart
├── screens/                    # الشاشات
│   ├── splash_screen.dart
│   ├── home_screen.dart
│   ├── auth/
│   │   ├── login_screen.dart
│   │   └── register_screen.dart
│   ├── products/
│   │   ├── products_screen.dart
│   │   └── product_detail_screen.dart
│   ├── auctions/
│   │   ├── auctions_screen.dart
│   │   └── auction_detail_screen.dart
│   ├── cart/
│   │   ├── cart_screen.dart
│   │   └── checkout_screen.dart
│   ├── wallet/
│   │   ├── wallet_screen.dart
│   │   └── remittances_screen.dart
│   └── user/
│       ├── dashboard_screen.dart
│       ├── orders_screen.dart
│       └── profile_screen.dart
├── widgets/                    # المكونات المعاد استخدامها
│   ├── product_card.dart
│   ├── auction_card.dart
│   ├── custom_app_bar.dart
│   └── loading_widget.dart
├── services/                   # الخدمات
│   ├── api_service.dart
│   ├── auth_service.dart
│   ├── notification_service.dart
│   └── storage_service.dart
├── utils/                      # الأدوات المساعدة
│   ├── theme.dart
│   ├── routes.dart
│   ├── constants.dart
│   └── helpers.dart
└── l10n/                       # الترجمة
    └── translations/
        ├── ar.json
        └── en.json
```

## التثبيت والتشغيل

### 1. المتطلبات
- Flutter SDK >= 3.0.0
- Dart SDK >= 3.0.0
- Android Studio / VS Code
- Android SDK (للأندرويد)
- Xcode (للـ iOS)

### 2. تثبيت Dependencies

```bash
cd flutter_app
flutter pub get
```

### 3. تشغيل التطبيق

**على محاكي أندرويد:**
```bash
flutter run
```

**على جهاز حقيقي:**
```bash
flutter run --release
```

### 4. بناء التطبيق

**APK (Android):**
```bash
flutter build apk --release
```

**App Bundle (Android):**
```bash
flutter build appbundle --release
```

**iOS:**
```bash
flutter build ios --release
```

## الإعدادات

### 1. API Configuration

في ملف `lib/services/api_service.dart`:

```dart
static const String baseUrl = 'https://api.storewallet.sd/api';
```

### 2. Firebase Configuration

1. إضافة `google-services.json` للأندرويد في `android/app/`
2. إضافة `GoogleService-Info.plist` لـ iOS في `ios/Runner/`

### 3. Stripe Configuration

في ملف `.env` أو كـ constants:

```dart
const String stripePublishableKey = 'your_stripe_key';
```

## المميزات الإضافية

### 🗺️ Maps & Location
- عرض موقع المنتجات والتجار
- تتبع الشحنات على الخريطة
- حساب المسافة والتكلفة

### 📸 صور المنتجات
- تحميل صور متعددة
- معاينة الصور
- ضغط الصور تلقائياً

### 🔔 الإشعارات
- إشعارات الطلبات
- إشعارات المزادات
- إشعارات المحفظة
- إشعارات التحويلات

### 📊 التقارير والإحصائيات
- Charts للمبيعات
- تقارير الأرباح
- إحصائيات المنتجات

### 🔐 الأمان
- Secure Storage للبيانات الحساسة
- Token-based Authentication
- SSL Pinning
- Biometric Authentication

## الاختبار

```bash
# Run tests
flutter test

# Run integration tests
flutter test integration_test
```

## النشر

### Google Play Store

1. إنشاء Signed APK/App Bundle
2. إعداد Store Listing
3. رفع التطبيق

### Apple App Store

1. إعداد App Store Connect
2. Archive وUpload
3. Submit للمراجعة

## الدعم الفني

- **Email:** support@storewallet.sd
- **Website:** https://storewallet.sd
- **Documentation:** https://docs.storewallet.sd

## الترخيص

MIT License

---

**Made with ❤️ for the Sudanese Market**
**صُنع بـ ❤️ للسوق السوداني**
