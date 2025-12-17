import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../models/cart_item.dart';
import 'package:hive/hive.dart';

class CartProvider with ChangeNotifier {
  List<CartItem> _items = [];
  double _shippingCost = 50.0;
  double _taxRate = 0.15;

  List<CartItem> get items => _items;
  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);
  double get shippingCost => _shippingCost;

  double get subtotal {
    return _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  }

  double get tax {
    return subtotal * _taxRate;
  }

  double get total {
    return subtotal + shippingCost + tax;
  }

  CartProvider() {
    _loadCart();
  }

  Future<void> _loadCart() async {
    try {
      final box = await Hive.openBox('cart');
      final savedCart = box.get('items');
      if (savedCart != null) {
        _items = (savedCart as List).map((item) => CartItem.fromJson(item)).toList();
        notifyListeners();
      }
    } catch (e) {
      print('Error loading cart: $e');
    }
  }

  Future<void> _saveCart() async {
    try {
      final box = await Hive.openBox('cart');
      await box.put('items', _items.map((item) => item.toJson()).toList());
    } catch (e) {
      print('Error saving cart: $e');
    }
  }

  void addItem(Product product, {int quantity = 1}) {
    final existingIndex = _items.indexWhere((item) => item.product.id == product.id);

    if (existingIndex >= 0) {
      _items[existingIndex] = CartItem(
        product: product,
        quantity: _items[existingIndex].quantity + quantity,
      );
    } else {
      _items.add(CartItem(product: product, quantity: quantity));
    }

    _saveCart();
    notifyListeners();
  }

  void removeItem(String productId) {
    _items.removeWhere((item) => item.product.id == productId);
    _saveCart();
    notifyListeners();
  }

  void updateQuantity(String productId, int quantity) {
    if (quantity <= 0) {
      removeItem(productId);
      return;
    }

    final index = _items.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      _items[index] = CartItem(
        product: _items[index].product,
        quantity: quantity,
      );
      _saveCart();
      notifyListeners();
    }
  }

  void clearCart() {
    _items.clear();
    _saveCart();
    notifyListeners();
  }
}
