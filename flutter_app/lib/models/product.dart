class Product {
  final String id;
  final String name;
  final String nameAr;
  final String description;
  final String descriptionAr;
  final double price;
  final double originalPrice;
  final double discountPercentage;
  final int stock;
  final String? mainImage;
  final List<String> images;
  final double rating;
  final int reviewsCount;
  final String categoryId;
  final String merchantId;
  final bool isActive;
  final bool isFeatured;

  Product({
    required this.id,
    required this.name,
    required this.nameAr,
    required this.description,
    required this.descriptionAr,
    required this.price,
    this.originalPrice = 0,
    this.discountPercentage = 0,
    required this.stock,
    this.mainImage,
    this.images = const [],
    this.rating = 0,
    this.reviewsCount = 0,
    required this.categoryId,
    required this.merchantId,
    this.isActive = true,
    this.isFeatured = false,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'].toString(),
      name: json['name'] ?? '',
      nameAr: json['name_ar'] ?? '',
      description: json['description'] ?? '',
      descriptionAr: json['description_ar'] ?? '',
      price: double.tryParse(json['price'].toString()) ?? 0,
      originalPrice: double.tryParse(json['original_price']?.toString() ?? '0') ?? 0,
      discountPercentage: double.tryParse(json['discount_percentage']?.toString() ?? '0') ?? 0,
      stock: int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      mainImage: json['main_image'],
      images: json['images'] != null
          ? (json['images'] as List).map((img) => img['image_url'].toString()).toList()
          : [],
      rating: double.tryParse(json['rating']?.toString() ?? '0') ?? 0,
      reviewsCount: int.tryParse(json['reviews_count']?.toString() ?? '0') ?? 0,
      categoryId: json['category_id'].toString(),
      merchantId: json['merchant_id'].toString(),
      isActive: json['is_active'] ?? true,
      isFeatured: json['is_featured'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'name_ar': nameAr,
      'description': description,
      'description_ar': descriptionAr,
      'price': price,
      'original_price': originalPrice,
      'discount_percentage': discountPercentage,
      'stock': stock,
      'main_image': mainImage,
      'images': images,
      'rating': rating,
      'reviews_count': reviewsCount,
      'category_id': categoryId,
      'merchant_id': merchantId,
      'is_active': isActive,
      'is_featured': isFeatured,
    };
  }
}
