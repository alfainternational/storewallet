import 'package:flutter/material.dart';
import 'package:easy_localization.dart';
import 'package:provider/provider.dart';
import '../providers/products_provider.dart';
import '../providers/auctions_provider.dart';
import '../utils/routes.dart';
import '../widgets/product_card.dart';
import '../widgets/auction_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final productsProvider = Provider.of<ProductsProvider>(context, listen: false);
    final auctionsProvider = Provider.of<AuctionsProvider>(context, listen: false);

    await Future.wait([
      productsProvider.fetchFeaturedProducts(),
      auctionsProvider.fetchActiveAuctions(),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.account_balance_wallet, color: Colors.blue),
            const SizedBox(width: 8),
            Text('app_name'.tr()),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.shopping_cart),
            onPressed: () => Navigator.pushNamed(context, Routes.cart),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Hero Section
            _buildHeroSection(),

            // Features
            _buildFeatures(),

            // Featured Products
            _buildFeaturedProducts(),

            // Active Auctions
            _buildActiveAuctions(),

            const SizedBox(height: 20),
          ],
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() => _currentIndex = index);

          switch (index) {
            case 0:
              // Home - already here
              break;
            case 1:
              Navigator.pushNamed(context, Routes.products);
              break;
            case 2:
              Navigator.pushNamed(context, Routes.auctions);
              break;
            case 3:
              Navigator.pushNamed(context, Routes.wallet);
              break;
            case 4:
              Navigator.pushNamed(context, Routes.dashboard);
              break;
          }
        },
        items: [
          BottomNavigationBarItem(
            icon: const Icon(Icons.home),
            label: 'nav.home'.tr(),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.shopping_bag),
            label: 'nav.products'.tr(),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.gavel),
            label: 'nav.auctions'.tr(),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.account_balance_wallet),
            label: 'nav.wallet'.tr(),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.dashboard),
            label: 'nav.dashboard'.tr(),
          ),
        ],
      ),
    );
  }

  Widget _buildHeroSection() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Colors.blue.shade700, Colors.purple.shade700],
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'welcome'.tr(),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 28,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'subtitle'.tr(),
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              _buildStat('70+', context.locale.languageCode == 'ar' ? 'مدينة' : 'Cities'),
              const SizedBox(width: 30),
              _buildStat('10', context.locale.languageCode == 'ar' ? 'عملات' : 'Currencies'),
              const SizedBox(width: 30),
              _buildStat('24/7', context.locale.languageCode == 'ar' ? 'دعم' : 'Support'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStat(String number, String label) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          number,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _buildFeatures() {
    final features = [
      {'icon': Icons.security, 'title': 'مدفوعات آمنة', 'titleEn': 'Secure Payments'},
      {'icon': Icons.local_shipping, 'title': 'شحن سريع', 'titleEn': 'Fast Shipping'},
      {'icon': Icons.swap_horiz, 'title': 'عملات متعددة', 'titleEn': 'Multi-Currency'},
      {'icon': Icons.headset_mic, 'title': 'دعم العملاء', 'titleEn': 'Support'},
    ];

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 20),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: features.map((feature) {
          return Column(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  feature['icon'] as IconData,
                  color: Colors.blue,
                  size: 30,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                context.locale.languageCode == 'ar'
                    ? feature['title'] as String
                    : feature['titleEn'] as String,
                style: const TextStyle(fontSize: 10),
                textAlign: TextAlign.center,
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildFeaturedProducts() {
    return Consumer<ProductsProvider>(
      builder: (context, provider, _) {
        if (provider.isLoading) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(20),
              child: CircularProgressIndicator(),
            ),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'products.featured_products'.tr(),
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  TextButton(
                    onPressed: () => Navigator.pushNamed(context, Routes.products),
                    child: Text('common.view_all'.tr()),
                  ),
                ],
              ),
            ),
            SizedBox(
              height: 280,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: provider.featuredProducts.length,
                itemBuilder: (context, index) {
                  final product = provider.featuredProducts[index];
                  return Padding(
                    padding: const EdgeInsets.only(right: 12),
                    child: SizedBox(
                      width: 180,
                      child: ProductCard(product: product),
                    ),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildActiveAuctions() {
    return Consumer<AuctionsProvider>(
      builder: (context, provider, _) {
        if (provider.isLoading) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(20),
              child: CircularProgressIndicator(),
            ),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'auctions.active_auctions'.tr(),
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  TextButton(
                    onPressed: () => Navigator.pushNamed(context, Routes.auctions),
                    child: Text('common.view_all'.tr()),
                  ),
                ],
              ),
            ),
            SizedBox(
              height: 240,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: provider.activeAuctions.length,
                itemBuilder: (context, index) {
                  final auction = provider.activeAuctions[index];
                  return Padding(
                    padding: const EdgeInsets.only(right: 12),
                    child: SizedBox(
                      width: 280,
                      child: AuctionCard(auction: auction),
                    ),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }
}
