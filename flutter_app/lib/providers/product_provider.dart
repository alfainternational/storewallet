import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class ProductProvider with ChangeNotifier {
  List<Product> _products = [];
  List<Product> _filteredProducts = [];
  bool _isLoading = false;
  String _error = '';
  int _currentPage = 1;
  int _totalPages = 1;

  List<Product> get products => _filteredProducts.isNotEmpty ? _filteredProducts : _products;
  bool get isLoading => _isLoading;
  String get error => _error;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;

  Future<void> fetchProducts({
    String? search,
    String? categoryId,
    double? minPrice,
    double? maxPrice,
    bool? inStock,
    String? sort,
    int page = 1,
  }) async {
    _isLoading = true;
    _error = '';
    notifyListeners();

    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': 20,
      };

      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (categoryId != null) queryParams['category_id'] = categoryId;
      if (minPrice != null) queryParams['min_price'] = minPrice;
      if (maxPrice != null) queryParams['max_price'] = maxPrice;
      if (inStock != null) queryParams['in_stock'] = inStock;
      if (sort != null) queryParams['sort'] = sort;

      final response = await ApiService.get('/products', queryParams: queryParams);

      if (response['success']) {
        _products = (response['products'] as List)
            .map((json) => Product.fromJson(json))
            .toList();
        _currentPage = response['current_page'] ?? 1;
        _totalPages = response['total_pages'] ?? 1;
      } else {
        _error = response['message'] ?? 'Failed to load products';
      }
    } catch (e) {
      _error = e.toString();
      print('Error fetching products: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<Product?> getProductById(String id) async {
    try {
      final response = await ApiService.get('/products/$id');
      if (response['success']) {
        return Product.fromJson(response['product']);
      }
    } catch (e) {
      print('Error fetching product: $e');
    }
    return null;
  }

  void searchProducts(String query) {
    if (query.isEmpty) {
      _filteredProducts = [];
    } else {
      _filteredProducts = _products
          .where((product) =>
              product.name.toLowerCase().contains(query.toLowerCase()) ||
              product.description.toLowerCase().contains(query.toLowerCase()))
          .toList();
    }
    notifyListeners();
  }

  void clearFilters() {
    _filteredProducts = [];
    notifyListeners();
  }
}
