<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\AuctionCategory;
use App\Services\AuctionService;
use App\Services\BiddingService;
use App\Services\AutoBidService;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    protected $auctionService;
    protected $biddingService;
    protected $autoBidService;

    public function __construct(
        AuctionService $auctionService,
        BiddingService $biddingService,
        AutoBidService $autoBidService
    ) {
        $this->middleware('auth')->except(['index', 'show']);
        $this->auctionService = $auctionService;
        $this->biddingService = $biddingService;
        $this->autoBidService = $autoBidService;
    }

    /**
     * Display auctions listing
     */
    public function index(Request $request)
    {
        $filters = [
            'type' => $request->get('type'),
            'category_id' => $request->get('category'),
            'city' => $request->get('city'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'sort_by' => $request->get('sort', 'created_at'),
            'sort_order' => $request->get('order', 'desc'),
            'per_page' => $request->get('per_page', 20)
        ];

        $auctions = $this->auctionService->getActiveAuctions($filters);
        $categories = AuctionCategory::where('active', true)->get();

        return view('auctions.index', compact('auctions', 'categories', 'filters'));
    }

    /**
     * Show auction details
     */
    public function show($id)
    {
        $auction = Auction::with([
            'user',
            'category',
            'images',
            'bids' => function($q) {
                $q->orderBy('bid_time', 'desc')->limit(10);
            },
            'questions' => function($q) {
                $q->where('is_public', true)->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Increment view count
        $auction->incrementViewCount();

        $isWatching = auth()->check() ? $auction->isWatchedBy(auth()->id()) : false;
        $userBids = auth()->check() ? $this->biddingService->getUserBidHistory($auction) : collect();

        return view('auctions.show', compact('auction', 'isWatching', 'userBids'));
    }

    /**
     * Show create auction form
     */
    public function create()
    {
        $categories = AuctionCategory::where('active', true)->whereNull('parent_id')->get();

        return view('auctions.create', compact('categories'));
    }

    /**
     * Store new auction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,delivery,international',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:auction_categories,id',
            'starting_price' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'required|integer|min:60',
            'images.*' => 'nullable|image|max:5120',
            // Add more validation rules
        ]);

        try {
            $images = $request->file('images');
            $auction = $this->auctionService->createAuction($validated, $images);

            return redirect()->route('auctions.show', $auction->id)
                ->with('success', 'Auction created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create auction: ' . $e->getMessage());
        }
    }

    /**
     * Publish auction
     */
    public function publish($id)
    {
        $auction = Auction::findOrFail($id);

        $this->authorize('update', $auction);

        try {
            $this->auctionService->publishAuction($auction);

            return redirect()->route('auctions.show', $auction->id)
                ->with('success', 'Auction published successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Place bid
     */
    public function placeBid(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $validated = $request->validate([
            'bid_amount' => 'required|numeric|min:0',
            'company_id' => 'nullable|exists:shipping_companies,id',
            'estimated_delivery_hours' => 'nullable|integer',
            'company_notes' => 'nullable|string'
        ]);

        try {
            $bid = $this->biddingService->placeBid(
                $auction,
                $validated['bid_amount'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Bid placed successfully',
                'bid' => $bid,
                'auction' => $auction->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Buy now
     */
    public function buyNow($id)
    {
        $auction = Auction::findOrFail($id);

        try {
            $bid = $this->biddingService->buyNow($auction);

            return redirect()->route('auctions.show', $auction->id)
                ->with('success', 'Purchase successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Watch/Unwatch auction
     */
    public function toggleWatch($id)
    {
        $auction = Auction::findOrFail($id);

        try {
            if ($auction->isWatchedBy(auth()->id())) {
                $this->auctionService->unwatchAuction($auction);
                $message = 'Removed from watchlist';
                $watching = false;
            } else {
                $this->auctionService->watchAuction($auction);
                $message = 'Added to watchlist';
                $watching = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'watching' => $watching
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Setup auto-bid
     */
    public function setupAutoBid(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $validated = $request->validate([
            'max_bid_amount' => 'required|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0',
            'only_last_hour' => 'nullable|boolean',
            'max_price_limit' => 'nullable|numeric|min:0'
        ]);

        try {
            $autoBid = $this->autoBidService->setupAutoBid(
                $auction,
                $validated['max_bid_amount'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Auto-bid configured successfully',
                'auto_bid' => $autoBid
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * My auctions
     */
    public function myAuctions()
    {
        $auctions = Auction::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('auctions.my-auctions', compact('auctions'));
    }

    /**
     * My bids
     */
    public function myBids()
    {
        $bids = \App\Models\AuctionBid::where('user_id', auth()->id())
            ->with('auction')
            ->orderBy('bid_time', 'desc')
            ->paginate(20);

        return view('auctions.my-bids', compact('bids'));
    }

    /**
     * My watchlist
     */
    public function myWatchlist()
    {
        $watchers = \App\Models\AuctionWatcher::where('user_id', auth()->id())
            ->with('auction')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('auctions.watchlist', compact('watchers'));
    }
}
