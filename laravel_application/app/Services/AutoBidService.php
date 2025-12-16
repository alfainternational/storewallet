<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionAutoBid;
use App\Models\AuctionActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoBidService
{
    /**
     * Setup auto-bidding for user
     *
     * @param Auction $auction
     * @param float $maxBidAmount
     * @param array $options
     * @return AuctionAutoBid
     */
    public function setupAutoBid(Auction $auction, $maxBidAmount, array $options = [])
    {
        $userId = auth()->id();

        // Validate
        if ($auction->user_id === $userId) {
            throw new Exception('Cannot auto-bid on your own auction');
        }

        if (!$auction->canAcceptBids()) {
            throw new Exception('Auction is not accepting bids');
        }

        // Create or update auto-bid
        $autoBid = AuctionAutoBid::updateOrCreate(
            [
                'auction_id' => $auction->id,
                'user_id' => $userId
            ],
            [
                'max_bid_amount' => $maxBidAmount,
                'bid_increment' => $options['bid_increment'] ?? $auction->min_bid_increment,
                'active' => true,
                'only_last_hour' => $options['only_last_hour'] ?? false,
                'max_price_limit' => $options['max_price_limit'] ?? null
            ]
        );

        // Log activity
        AuctionActivityLog::logActivity(
            $auction->id,
            'auto_bid_setup',
            'Auto-bid configured',
            ['max_amount' => $maxBidAmount],
            $userId
        );

        // Try to place initial bid if possible
        $this->tryPlaceAutoBid($autoBid, $auction->current_price);

        return $autoBid;
    }

    /**
     * Deactivate auto-bid
     */
    public function deactivateAutoBid(AuctionAutoBid $autoBid)
    {
        $autoBid->deactivate();

        AuctionActivityLog::logActivity(
            $autoBid->auction_id,
            'auto_bid_deactivated',
            'Auto-bid deactivated',
            null,
            $autoBid->user_id
        );
    }

    /**
     * Process auto-bids when new manual bid is placed
     *
     * @param Auction $auction
     * @param float $currentPrice
     */
    public function processAutoBids(Auction $auction, $currentPrice)
    {
        // Get all active auto-bids for this auction
        $autoBids = $auction->autoBids()
            ->where('active', true)
            ->where('user_id', '!=', $auction->winningBid->user_id ?? null) // Exclude current winner
            ->get();

        foreach ($autoBids as $autoBid) {
            try {
                $this->tryPlaceAutoBid($autoBid, $currentPrice);
            } catch (Exception $e) {
                Log::warning('Auto-bid failed for user #' . $autoBid->user_id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Try to place auto-bid
     */
    protected function tryPlaceAutoBid(AuctionAutoBid $autoBid, $currentPrice)
    {
        $auction = $autoBid->auction;

        // Check if can place auto-bid
        if (!$autoBid->canPlaceAutoBid($currentPrice)) {
            return false;
        }

        // Calculate next bid amount
        $nextBid = $autoBid->getNextBidAmount($currentPrice);

        // Ensure next bid is valid
        if ($auction->is_reverse_auction) {
            if ($nextBid >= $currentPrice) {
                return false; // For delivery, must be lower
            }
        } else {
            $minimumBid = $currentPrice + $auction->min_bid_increment;
            if ($nextBid < $minimumBid) {
                $nextBid = $minimumBid;
            }
        }

        // Check if exceeds max bid amount
        if ($nextBid > $autoBid->max_bid_amount) {
            $nextBid = $autoBid->max_bid_amount;
        }

        DB::beginTransaction();

        try {
            // Create auto-bid
            $bid = AuctionBid::create([
                'auction_id' => $auction->id,
                'user_id' => $autoBid->user_id,
                'bid_amount' => $nextBid,
                'max_bid_amount' => $autoBid->max_bid_amount,
                'is_auto_bid' => true,
                'status' => 'active',
                'bid_time' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => 'AutoBid System'
            ]);

            // Update auction
            $auction->current_price = $nextBid;
            $auction->increment('bid_count');
            $auction->save();

            // Mark previous winning bid as outbid
            $previousWinningBid = $auction->winningBid;
            if ($previousWinningBid && $previousWinningBid->id !== $bid->id) {
                $previousWinningBid->markAsOutbid();
            }

            // Mark this bid as winning
            $bid->markAsWinning();

            // Update auto-bid record
            $autoBid->recordBid($nextBid);

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'auto_bid_placed',
                'Auto-bid placed: ' . $nextBid,
                [
                    'bid_id' => $bid->id,
                    'user_id' => $autoBid->user_id,
                    'amount' => $nextBid,
                    'max_amount' => $autoBid->max_bid_amount
                ],
                $autoBid->user_id
            );

            // Check if reached max amount, deactivate if so
            if ($nextBid >= $autoBid->max_bid_amount) {
                $autoBid->deactivate();
            }

            DB::commit();

            return $bid;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to place auto-bid: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user's auto-bids
     */
    public function getUserAutoBids($userId = null)
    {
        $userId = $userId ?? auth()->id();

        return AuctionAutoBid::where('user_id', $userId)
            ->with('auction')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
