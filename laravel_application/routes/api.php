<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\RemittanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Auctions
Route::get('/auctions', [AuctionController::class, 'index']);
Route::get('/auctions/{id}', [AuctionController::class, 'show']);

// Categories
Route::get('/categories', function() {
    return response()->json([
        'success' => true,
        'categories' => \App\Models\Category::with('subcategories')->whereNull('parent_id')->get()
    ]);
});

// Cities
Route::get('/cities', function() {
    return response()->json([
        'success' => true,
        'cities' => \App\Models\SudaneseCity::orderBy('name')->get()
    ]);
});

// Currencies
Route::get('/currencies', function() {
    return response()->json([
        'success' => true,
        'currencies' => \App\Models\Currency::where('is_active', true)->get()
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);
    Route::post('/user/avatar', [AuthController::class, 'uploadAvatar']);

    // Wallet
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);

    // Auction Bids
    Route::post('/auctions/{id}/bid', [AuctionController::class, 'placeBid']);

    // Remittances
    Route::get('/remittances', [RemittanceController::class, 'index']);
    Route::get('/remittances/{id}', [RemittanceController::class, 'show']);
    Route::post('/remittances', [RemittanceController::class, 'store']);

    // Reviews
    Route::post('/products/{id}/reviews', function($id, Request $request) {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $review = \App\Models\Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'review' => $review], 201);
    });

    // User Dashboard
    Route::get('/user/dashboard', function(Request $request) {
        $user = $request->user();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total'),
            'active_auctions' => \App\Models\AuctionBid::where('user_id', $user->id)
                ->whereHas('auction', fn($q) => $q->where('status', 'active'))
                ->distinct('auction_id')
                ->count(),
        ];

        $recentOrders = $user->orders()->with('items.product')->latest()->limit(5)->get();
        $activeBids = \App\Models\AuctionBid::with('auction.product')
            ->where('user_id', $user->id)
            ->whereHas('auction', fn($q) => $q->where('status', 'active'))
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'active_bids' => $activeBids,
        ]);
    });

    // Merchant routes
    Route::middleware('role:merchant')->prefix('merchant')->group(function () {
        // Products
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Auctions
        Route::post('/auctions', [AuctionController::class, 'store']);
        Route::put('/auctions/{id}', [AuctionController::class, 'update']);

        // Dashboard
        Route::get('/dashboard', function(Request $request) {
            $merchant = $request->user()->merchant;

            $stats = [
                'total_products' => $merchant->products()->count(),
                'total_sales' => \App\Models\OrderItem::where('merchant_id', $merchant->id)->sum('total'),
                'pending_orders' => \App\Models\OrderItem::where('merchant_id', $merchant->id)
                    ->whereHas('order', fn($q) => $q->where('status', 'pending'))
                    ->count(),
                'rating' => $merchant->rating ?? 0,
            ];

            $recentOrders = \App\Models\Order::whereHas('items', fn($q) => $q->where('merchant_id', $merchant->id))
                ->with('user', 'items')
                ->latest()
                ->limit(10)
                ->get();

            $lowStockProducts = $merchant->products()->where('stock', '<', 10)->get();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'low_stock_products' => $lowStockProducts,
            ]);
        });

        // Orders
        Route::get('/orders', function(Request $request) {
            $merchant = $request->user()->merchant;
            $orders = \App\Models\Order::whereHas('items', fn($q) => $q->where('merchant_id', $merchant->id))
                ->with('user', 'items.product')
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'orders' => $orders->items(),
                'total_pages' => $orders->lastPage(),
            ]);
        });
    });

    // Shipping Company routes
    Route::middleware('role:shipping_company')->prefix('shipper')->group(function () {
        Route::get('/dashboard', function(Request $request) {
            $shippingCompany = $request->user()->shippingCompany;

            $stats = [
                'total_shipments' => $shippingCompany->shipments()->count(),
                'active_shipments' => $shippingCompany->shipments()->whereIn('status', ['pending', 'picked_up', 'in_transit'])->count(),
                'completed_shipments' => $shippingCompany->shipments()->where('status', 'delivered')->count(),
                'rating' => $shippingCompany->rating ?? 0,
            ];

            return response()->json(['success' => true, 'stats' => $stats]);
        });

        Route::get('/shipments', function(Request $request) {
            $shipments = $request->user()->shippingCompany->shipments()
                ->with('order')
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'shipments' => $shipments->items(),
                'total_pages' => $shipments->lastPage(),
            ]);
        });

        Route::put('/shipments/{id}/status', function(Request $request, $id) {
            $shipment = \App\Models\Shipment::findOrFail($id);

            $shipment->update([
                'status' => $request->status,
                'current_lat' => $request->current_lat,
                'current_lng' => $request->current_lng,
            ]);

            return response()->json(['success' => true, 'shipment' => $shipment]);
        });
    });
});

// Fallback route
Route::fallback(function() {
    return response()->json(['success' => false, 'message' => 'Route not found'], 404);
});
