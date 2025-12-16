<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionAutoBid;
use App\Models\AuctionActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class BiddingService
{
    protected $walletService;
    protected $escrowService;
    protected $autoBidService;

    public function __construct(
        WalletService $walletService,
        EscrowService $escrowService,
        AutoBidService $autoBidService
    ) {
        $this->walletService = $walletService;
        $this->escrowService = $escrowService;
        $this->autoBidService = $autoBidService;
    }

    /**
     * Place a bid on auction
     *
     * @param Auction $auction
     * @param float $bidAmount
     * @param array $additionalData
     * @return AuctionBid
     */
    public function placeBid(Auction $auction, $bidAmount, array $additionalData = [])
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            // Validations
            $this->validateBid($auction, $bidAmount, $userId);

            // Check wallet balance
            $user = auth()->user();
            if (!$user->wallet_user_id) {
                throw new Exception('Wallet account required');
            }

            $walletUser = $this->walletService->getWalletUser($user->email);
            if (!$walletUser || $walletUser->balance < $bidAmount) {
                throw new Exception('Insufficient wallet balance');
            }

            // Create bid
            $bid = AuctionBid::create([
                'auction_id' => $auction->id,
                'user_id' => $userId,
                'bid_amount' => $bidAmount,
                'company_id' => $additionalData['company_id'] ?? null,
                'estimated_delivery_hours' => $additionalData['estimated_delivery_hours'] ?? null,
                'company_notes' => $additionalData['company_notes'] ?? null,
                'status' => 'active',
                'bid_time' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Update auction
            $auction->current_price = $bidAmount;
            $auction->increment('bid_count');
            $auction->save();

            // Mark previous winning bid as outbid
            $previousWinningBid = $auction->winningBid;
            if ($previousWinningBid && $previousWinningBid->id !== $bid->id) {
                $previousWinningBid->markAsOutbid();
            }

            // Mark this bid as winning
            $bid->markAsWinning();

            // Check if auto-extend is needed
            if ($auction->auto_extend && $auction->end_time->diffInMinutes(now()) <= 5) {
                $auction->extendTime();

                AuctionActivityLog::logActivity(
                    $auction->id,
                    'auction_extended',
                    'Auction extended by ' . $auction->extension_minutes . ' minutes due to late bid',
                    ['new_end_time' => $auction->end_time]
                );
            }

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'bid_placed',
                'New bid placed: ' . $bidAmount,
                [
                    'bid_id' => $bid->id,
                    'user_id' => $userId,
                    'amount' => $bidAmount
                ],
                $userId
            );

            // Trigger auto-bids from other users
            $this->autoBidService->processAutoBids($auction, $bidAmount);

            // TODO: Send notifications to watchers and outbid users

            DB::commit();

            return $bid;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to place bid: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate bid
     */
    protected function validateBid(Auction $auction, $bidAmount, $userId)
    {
        // Check if auction can accept bids
        if (!$auction->canAcceptBids()) {
            throw new Exception('Auction is not accepting bids');
        }

        // Check if user is the seller
        if ($auction->user_id === $userId) {
            throw new Exception('You cannot bid on your own auction');
        }

        // Check minimum bid increment
        if ($auction->is_reverse_auction) {
            // For delivery auctions, bid must be lower
            if ($bidAmount >= $auction->current_price) {
                throw new Exception('Bid must be lower than current price for delivery auctions');
            }

            // Check if below reserve price (max budget)
            if ($auction->reserve_price && $bidAmount > $auction->reserve_price) {
                throw new Exception('Bid exceeds maximum budget');
            }
        } else {
            // For product auctions, bid must be higher
            $minimumBid = $auction->current_price + $auction->min_bid_increment;

            if ($bidAmount < $minimumBid) {
                throw new Exception('Bid must be at least ' . $minimumBid);
            }

            // Check if meets reserve price
            if ($auction->reserve_price && $bidAmount < $auction->reserve_price) {
                // Allow bid but flag that reserve not met
            }
        }

        // Check for private auction
        if ($auction->private_auction) {
            // TODO: Check if user is invited
        }

        // Check for verified bidders only
        if ($auction->verified_bidders_only) {
            if (!auth()->user()->verified) {
                throw new Exception('Only verified users can bid on this auction');
            }
        }

        return true;
    }

    /**
     * Buy now (instant purchase)
     */
    public function buyNow(Auction $auction)
    {
        DB::beginTransaction();

        try {
            if (!$auction->buy_now_price) {
                throw new Exception('Buy now not available for this auction');
            }

            if (!$auction->canAcceptBids()) {
                throw new Exception('Auction is not active');
            }

            $userId = auth()->id();

            // Place bid at buy now price
            $bid = $this->placeBid($auction, $auction->buy_now_price);

            // Immediately end auction and set winner
            $auction->status = 'ended';
            $auction->winner_user_id = $userId;
            $auction->winning_price = $auction->buy_now_price;
            $auction->won_at = now();
            $auction->end_time = now();
            $auction->save();

            // Update bid status
            $bid->update(['status' => 'won']);

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'buy_now',
                'Auction ended via Buy Now',
                ['price' => $auction->buy_now_price],
                $userId
            );

            DB::commit();

            return $bid;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to buy now: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retract bid (if allowed)
     */
    public function retractBid(AuctionBid $bid)
    {
        DB::beginTransaction();

        try {
            // Check if can retract
            $auction = $bid->auction;

            // Usually bids can't be retracted, but add logic if needed
            $minutesSinceBid = now()->diffInMinutes($bid->bid_time);

            if ($minutesSinceBid > 5) {
                throw new Exception('Cannot retract bid after 5 minutes');
            }

            if ($bid->is_winning) {
                throw new Exception('Cannot retract winning bid');
            }

            // Retract bid
            $bid->retract();

            // Decrement bid count
            $auction->decrement('bid_count');

            // Recalculate winning bid
            $this->recalculateWinningBid($auction);

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'bid_retracted',
                'Bid retracted',
                ['bid_id' => $bid->id],
                $bid->user_id
            );

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to retract bid: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Recalculate winning bid after retraction
     */
    protected function recalculateWinningBid(Auction $auction)
    {
        // Get highest active bid (or lowest for reverse)
        $winningBid = AuctionBid::where('auction_id', $auction->id)
            ->where('status', 'active')
            ->orderBy('bid_amount', $auction->is_reverse_auction ? 'asc' : 'desc')
            ->first();

        if ($winningBid) {
            $winningBid->markAsWinning();
            $auction->current_price = $winningBid->bid_amount;
            $auction->save();
        } else {
            $auction->current_price = $auction->starting_price;
            $auction->save();
        }
    }

    /**
     * Get user's bid history for auction
     */
    public function getUserBidHistory(Auction $auction, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', $userId)
            ->orderBy('bid_time', 'desc')
            ->get();
    }

    /**
     * Get auction bid history
     */
    public function getAuctionBidHistory(Auction $auction, $limit = null)
    {
        $query = AuctionBid::where('auction_id', $auction->id)
            ->with('user')
            ->orderBy('bid_time', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Accept bid (for delivery auctions - manual acceptance)
     */
    public function acceptBid(Auction $auction, AuctionBid $bid)
    {
        DB::beginTransaction();

        try {
            // Only auction owner can accept
            if ($auction->user_id !== auth()->id()) {
                throw new Exception('Unauthorized');
            }

            // Only for delivery/international auctions
            if (!in_array($auction->type, ['delivery', 'international'])) {
                throw new Exception('Manual bid acceptance only for delivery auctions');
            }

            // End auction and set winner
            $auction->status = 'completed';
            $auction->winner_user_id = $bid->user_id;
            $auction->winning_price = $bid->bid_amount;
            $auction->won_at = now();
            $auction->end_time = now();
            $auction->save();

            // Update bid statuses
            $bid->update(['status' => 'won', 'is_winning' => true]);

            $auction->bids()->where('id', '!=', $bid->id)
                ->update(['status' => 'lost', 'is_winning' => false]);

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'bid_accepted',
                'Bid accepted manually',
                ['bid_id' => $bid->id, 'amount' => $bid->bid_amount]
            );

            DB::commit();

            return $bid;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept bid: ' . $e->getMessage());
            throw $e;
        }
    }
}
