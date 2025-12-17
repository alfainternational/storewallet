<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['merchant', 'category', 'images'])
            ->active()
            ->latest();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('sales_count', 'desc');
                    break;
                case 'newest':
                    $query->latest();
                    break;
            }
        }

        $products = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'products' => $products->items(),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'total_pages' => $products->lastPage(),
        ]);
    }

    public function show($id)
    {
        $product = Product::with([
            'merchant',
            'category',
            'images',
            'variants',
            'reviews.user'
        ])->findOrFail($id);

        $product->increment('views_count');

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'product' => $product,
            'related_products' => $relatedProducts,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $merchant = $request->user()->merchant;
        if (!$merchant) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $discount = 0;
        if ($request->original_price && $request->price < $request->original_price) {
            $discount = round((($request->original_price - $request->price) / $request->original_price) * 100, 2);
        }

        $product = Product::create([
            'merchant_id' => $merchant->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'discount_percentage' => $discount,
            'stock' => $request->stock,
            'sku' => 'PRD-' . strtoupper(Str::random(8)),
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'is_active' => true,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => '/storage/' . $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json(['success' => true, 'product' => $product->load(['images'])], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->merchant_id !== $request->user()->merchant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'description_ar' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->merchant_id !== $request->user()->merchant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product->update(['is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
