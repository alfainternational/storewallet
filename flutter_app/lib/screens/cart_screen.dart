import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import 'checkout_screen.dart';

class CartScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('سلة التسوق'.tr),
        actions: [
          Consumer<CartProvider>(
            builder: (context, cart, child) {
              if (cart.itemCount > 0) {
                return TextButton(
                  onPressed: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        title: Text('تفريغ السلة'.tr),
                        content: Text('هل أنت متأكد من تفريغ سلة التسوق؟'.tr),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: Text('إلغاء'.tr),
                          ),
                          TextButton(
                            onPressed: () {
                              cart.clearCart();
                              Navigator.pop(context);
                            },
                            child: Text('تفريغ'.tr, style: TextStyle(color: Colors.red)),
                          ),
                        ],
                      ),
                    );
                  },
                  child: Text('تفريغ'.tr, style: TextStyle(color: Colors.red)),
                );
              }
              return SizedBox.shrink();
            },
          ),
        ],
      ),
      body: Consumer<CartProvider>(
        builder: (context, cart, child) {
          if (cart.items.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.shopping_cart_outlined, size: 100, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('سلة التسوق فارغة'.tr, style: TextStyle(fontSize: 20, color: Colors.grey)),
                  SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => Navigator.pushNamed(context, '/products'),
                    child: Text('ابدأ التسوق'.tr),
                  ),
                ],
              ),
            );
          }

          return Column(
            children: [
              Expanded(
                child: ListView.builder(
                  padding: EdgeInsets.all(16),
                  itemCount: cart.items.length,
                  itemBuilder: (context, index) {
                    final item = cart.items[index];
                    return Card(
                      margin: EdgeInsets.only(bottom: 12),
                      child: Padding(
                        padding: EdgeInsets.all(12),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(
                                item.product.mainImage ?? 'https://via.placeholder.com/80',
                                width: 80,
                                height: 80,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return Container(
                                    width: 80,
                                    height: 80,
                                    color: Colors.grey[300],
                                    child: Icon(Icons.image, color: Colors.grey),
                                  );
                                },
                              ),
                            ),
                            SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item.product.name,
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  SizedBox(height: 4),
                                  Text(
                                    '${item.product.price} SDG',
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: Theme.of(context).primaryColor,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  SizedBox(height: 8),
                                  Row(
                                    children: [
                                      IconButton(
                                        icon: Icon(Icons.remove_circle_outline),
                                        onPressed: () {
                                          if (item.quantity > 1) {
                                            cart.updateQuantity(item.product.id, item.quantity - 1);
                                          }
                                        },
                                        constraints: BoxConstraints(),
                                        padding: EdgeInsets.zero,
                                      ),
                                      Padding(
                                        padding: EdgeInsets.symmetric(horizontal: 12),
                                        child: Text(
                                          item.quantity.toString(),
                                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                      IconButton(
                                        icon: Icon(Icons.add_circle_outline),
                                        onPressed: () {
                                          cart.updateQuantity(item.product.id, item.quantity + 1);
                                        },
                                        constraints: BoxConstraints(),
                                        padding: EdgeInsets.zero,
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            Column(
                              children: [
                                IconButton(
                                  icon: Icon(Icons.delete_outline, color: Colors.red),
                                  onPressed: () {
                                    cart.removeItem(item.product.id);
                                  },
                                ),
                                SizedBox(height: 8),
                                Text(
                                  '${item.totalPrice} SDG',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
              Container(
                padding: EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black12,
                      blurRadius: 8,
                      offset: Offset(0, -2),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('المجموع الفرعي:'.tr, style: TextStyle(fontSize: 16)),
                        Text('${cart.subtotal.toStringAsFixed(2)} SDG', style: TextStyle(fontSize: 16)),
                      ],
                    ),
                    SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('الشحن:'.tr, style: TextStyle(fontSize: 16)),
                        Text('${cart.shippingCost.toStringAsFixed(2)} SDG', style: TextStyle(fontSize: 16)),
                      ],
                    ),
                    SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('الضريبة:'.tr, style: TextStyle(fontSize: 16)),
                        Text('${cart.tax.toStringAsFixed(2)} SDG', style: TextStyle(fontSize: 16)),
                      ],
                    ),
                    Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'الإجمالي:'.tr,
                          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          '${cart.total.toStringAsFixed(2)} SDG',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).primaryColor,
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => CheckoutScreen()),
                          );
                        },
                        child: Text('إتمام الطلب'.tr, style: TextStyle(fontSize: 18)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
