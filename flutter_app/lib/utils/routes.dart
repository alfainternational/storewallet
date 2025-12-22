import 'package:flutter/material.dart';
import '../screens/splash_screen.dart';
import '../screens/home_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/products/products_screen.dart';
import '../screens/products/product_detail_screen.dart';
import '../screens/auctions/auctions_screen.dart';
import '../screens/auctions/auction_detail_screen.dart';
import '../screens/cart/cart_screen.dart';
import '../screens/cart/checkout_screen.dart';
import '../screens/wallet/wallet_screen.dart';
import '../screens/wallet/remittances_screen.dart';
import '../screens/user/dashboard_screen.dart';
import '../screens/user/orders_screen.dart';
import '../screens/user/profile_screen.dart';

class Routes {
  static const String splash = '/';
  static const String home = '/home';
  static const String login = '/login';
  static const String register = '/register';
  static const String products = '/products';
  static const String productDetail = '/products/detail';
  static const String auctions = '/auctions';
  static const String auctionDetail = '/auctions/detail';
  static const String cart = '/cart';
  static const String checkout = '/checkout';
  static const String wallet = '/wallet';
  static const String remittances = '/remittances';
  static const String dashboard = '/dashboard';
  static const String orders = '/orders';
  static const String profile = '/profile';

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return MaterialPageRoute(builder: (_) => const SplashScreen());

      case home:
        return MaterialPageRoute(builder: (_) => const HomeScreen());

      case login:
        return MaterialPageRoute(builder: (_) => const LoginScreen());

      case register:
        return MaterialPageRoute(builder: (_) => const RegisterScreen());

      case products:
        return MaterialPageRoute(builder: (_) => const ProductsScreen());

      case productDetail:
        final productId = settings.arguments as int;
        return MaterialPageRoute(
          builder: (_) => ProductDetailScreen(productId: productId),
        );

      case auctions:
        return MaterialPageRoute(builder: (_) => const AuctionsScreen());

      case auctionDetail:
        final auctionId = settings.arguments as int;
        return MaterialPageRoute(
          builder: (_) => AuctionDetailScreen(auctionId: auctionId),
        );

      case cart:
        return MaterialPageRoute(builder: (_) => const CartScreen());

      case checkout:
        return MaterialPageRoute(builder: (_) => const CheckoutScreen());

      case wallet:
        return MaterialPageRoute(builder: (_) => const WalletScreen());

      case remittances:
        return MaterialPageRoute(builder: (_) => const RemittancesScreen());

      case dashboard:
        return MaterialPageRoute(builder: (_) => const DashboardScreen());

      case orders:
        return MaterialPageRoute(builder: (_) => const OrdersScreen());

      case profile:
        return MaterialPageRoute(builder: (_) => const ProfileScreen());

      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('Route not found: ${settings.name}'),
            ),
          ),
        );
    }
  }
}
