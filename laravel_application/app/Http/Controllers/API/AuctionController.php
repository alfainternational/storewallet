<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['product', 'merchant', 'bids'])
            ->where('is_active', true);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('bid_type')) {
            $query->where('bid_type', $request->bid_type);
        }

        if ($request->has('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $auctions = $query->latest()->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'auctions' => $auctions->items(),
            'total_pages' => $auctions->lastPage(),
        ]);
    }

    public function show($id)
    {
        $auction = Auction::with([
            'product',
            'merchant',
            'bids.user'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'auction' => $auction,
            'bids' => $auction->bids()->with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'bid_type' => 'required|in:lowest_bid,highest_bid',
            'start_price' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'terms' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $merchant = $request->user()->merchant;
        if (!$merchant) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $auction = Auction::create([
            'merchant_id' => $merchant->id,
            'product_id' => $request->product_id,
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'bid_type' => $request->bid_type,
            'start_price' => $request->start_price,
            'current_bid' => $request->start_price,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'terms' => $request->terms,
            'status' => 'active',
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'auction' => $auction], 201);
    }

    public function placeBid(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $auction = Auction::findOrFail($id);

        if ($auction->status !== 'active' || now() > $auction->end_time) {
            return response()->json(['success' => false, 'message' => 'Auction is not active'], 422);
        }

        if ($auction->bid_type === 'lowest_bid') {
            if ($request->amount >= $auction->current_bid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid must be lower than current bid'
                ], 422);
            }
        } else {
            if ($request->amount <= $auction->current_bid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bid must be higher than current bid'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $bid = AuctionBid::create([
                'auction_id' => $auction->id,
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
            ]);

            $auction->update([
                'current_bid' => $request->amount,
                'winner_id' => $request->user()->id,
            ]);

            $auction->increment('bids_count');

            DB::commit();

            return response()->json(['success' => true, 'bid' => $bid]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to place bid'], 500);
        }
    }
}
