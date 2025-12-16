<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionAutoBid
 * @package App\Models
 */
class AuctionAutoBid extends Model
{
    public $table = 'auction_auto_bids';

    public $fillable = [
        'auction_id',
        'user_id',
        'max_bid_amount',
        'bid_increment',
        'active',
        'only_last_hour',
        'max_price_limit',
        'bids_placed',
        'current_bid_amount'
    ];

    protected $casts = [
        'max_bid_amount' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'max_price_limit' => 'decimal:2',
        'current_bid_amount' => 'decimal:2',
        'active' => 'boolean',
        'only_last_hour' => 'boolean',
        'bids_placed' => 'integer'
    ];

    /**
     * Get auction
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    /**
     * Get user
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Check if can place auto-bid
     */
    public function canPlaceAutoBid($currentPrice)
    {
        if (!$this->active) {
            return false;
        }

        if ($this->only_last_hour) {
            $timeRemaining = $this->auction->end_time->diffInHours(now());
            if ($timeRemaining > 1) {
                return false;
            }
        }

        if ($this->max_price_limit && $currentPrice >= $this->max_price_limit) {
            return false;
        }

        if ($this->current_bid_amount >= $this->max_bid_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate next bid amount
     */
    public function getNextBidAmount($currentPrice)
    {
        $nextBid = $currentPrice + $this->bid_increment;

        if ($nextBid > $this->max_bid_amount) {
            $nextBid = $this->max_bid_amount;
        }

        return $nextBid;
    }

    /**
     * Record placed bid
     */
    public function recordBid($amount)
    {
        $this->increment('bids_placed');
        $this->current_bid_amount = $amount;
        $this->save();
    }

    /**
     * Deactivate auto-bid
     */
    public function deactivate()
    {
        $this->active = false;
        $this->save();
    }
}
