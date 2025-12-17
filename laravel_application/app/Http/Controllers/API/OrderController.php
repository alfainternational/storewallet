<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\EscrowTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'user', 'shippingCity'])
            ->where('user_id', $request->user()->id)
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'orders' => $orders->items(),
            'total' => $orders->total(),
            'total_pages' => $orders->lastPage(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'shippingCity'])->findOrFail($id);

        if ($order->user_id !== $request->user()->id &&
            !$order->items->contains(fn($item) => $item->product->merchant_id === $request->user()->merchant?->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:wallet,xcash,bankak,e15,sudanipay,stripe,cod',
            'shipping_address' => 'required|string',
            'shipping_city_id' => 'required|exists:sudanese_cities,id',
            'shipping_phone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $items = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name}"
                    ], 422);
                }

                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $itemTotal,
                ];
            }

            $shippingCost = 50; // Fixed shipping for now
            $taxRate = 0.15; // 15% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $shippingCost + $tax;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => 'pending',
                'payment_status' => $request->payment_method === 'cod' ? 'unpaid' : 'pending',
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'shipping_city_id' => $request->shipping_city_id,
                'shipping_phone' => $request->shipping_phone,
                'notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'merchant_id' => $item['product']->merchant_id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                    'sku' => $item['product']->sku,
                ]);

                $item['product']->decrementStock($item['quantity']);
            }

            // Create escrow transaction
            EscrowTransaction::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'amount' => $total,
                'status' => 'holding',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => $order->load(['items.product']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Release escrow
            $order->escrowTransaction?->update(['status' => 'refunded']);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Order cancelled successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order'
            ], 500);
        }
    }
}
