import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/product_provider.dart';
import '../models/product.dart';
import 'product_detail_screen.dart';

class ProductsScreen extends StatefulWidget {
  @override
  _ProductsScreenState createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = '';
  String _sortBy = 'newest';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ProductProvider>().fetchProducts();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('المنتجات'.tr),
        actions: [
          IconButton(
            icon: Icon(Icons.filter_list),
            onPressed: _showFilterSheet,
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'ابحث عن المنتجات...'.tr,
                prefixIcon: Icon(Icons.search),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onChanged: (value) {
                context.read<ProductProvider>().searchProducts(value);
              },
            ),
          ),
          Expanded(
            child: Consumer<ProductProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return Center(child: CircularProgressIndicator());
                }

                if (provider.products.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.shopping_bag_outlined, size: 80, color: Colors.grey),
                        SizedBox(height: 16),
                        Text('لا توجد منتجات'.tr, style: TextStyle(fontSize: 18, color: Colors.grey)),
                      ],
                    ),
                  );
                }

                return GridView.builder(
                  padding: EdgeInsets.all(16),
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.75,
                    crossAxisSpacing: 16,
                    mainAxisSpacing: 16,
                  ),
                  itemCount: provider.products.length,
                  itemBuilder: (context, index) {
                    final product = provider.products[index];
                    return _buildProductCard(product);
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductCard(Product product) {
    return Card(
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 2,
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => ProductDetailScreen(productId: product.id),
            ),
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Container(
                    width: double.infinity,
                    child: Image.network(
                      product.mainImage ?? 'https://via.placeholder.com/200',
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          color: Colors.grey[300],
                          child: Icon(Icons.image, size: 50, color: Colors.grey),
                        );
                      },
                    ),
                  ),
                  if (product.discountPercentage > 0)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          '-${product.discountPercentage}%',
                          style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 4)],
                      ),
                      child: IconButton(
                        icon: Icon(Icons.favorite_border, size: 20),
                        onPressed: () {
                          // Add to wishlist
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  ),
                  SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.star, size: 16, color: Colors.amber),
                      SizedBox(width: 4),
                      Text(
                        product.rating.toString(),
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      Spacer(),
                      if (product.stock > 0)
                        Text(
                          'متوفر'.tr,
                          style: TextStyle(fontSize: 12, color: Colors.green),
                        )
                      else
                        Text(
                          'غير متوفر'.tr,
                          style: TextStyle(fontSize: 12, color: Colors.red),
                        ),
                    ],
                  ),
                  SizedBox(height: 8),
                  Row(
                    children: [
                      Text(
                        '${product.price} SDG',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                      if (product.originalPrice > product.price) ...[
                        SizedBox(width: 8),
                        Text(
                          '${product.originalPrice} SDG',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      builder: (context) {
        return Container(
          padding: EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'الفلاتر'.tr,
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 16),
              Text('الترتيب حسب:'.tr),
              ListTile(
                title: Text('الأحدث'.tr),
                leading: Radio(value: 'newest', groupValue: _sortBy, onChanged: (value) {
                  setState(() => _sortBy = value.toString());
                  Navigator.pop(context);
                }),
              ),
              ListTile(
                title: Text('الأقل سعراً'.tr),
                leading: Radio(value: 'price_low', groupValue: _sortBy, onChanged: (value) {
                  setState(() => _sortBy = value.toString());
                  Navigator.pop(context);
                }),
              ),
              ListTile(
                title: Text('الأعلى سعراً'.tr),
                leading: Radio(value: 'price_high', groupValue: _sortBy, onChanged: (value) {
                  setState(() => _sortBy = value.toString());
                  Navigator.pop(context);
                }),
              ),
            ],
          ),
        );
      },
    );
  }
}
