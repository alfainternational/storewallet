<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['merchant', 'images', 'category'])
            ->active()
            ->inStock();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['merchant', 'images', 'variants', 'reviews.user'])
            ->findOrFail($id);

        $product->incrementViews();

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = $request->user()->merchant->products()->create($validated);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('merchant_id', $request->user()->merchant->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json($product);
    }
}
