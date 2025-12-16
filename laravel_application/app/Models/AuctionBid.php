<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionBid
 * @package App\Models
 */
class AuctionBid extends Model
{
    public $table = 'auction_bids';

    public $fillable = [
        'auction_id',
        'user_id',
        'bid_amount',
        'max_bid_amount',
        'is_auto_bid',
        'company_id',
        'estimated_delivery_hours',
        'company_notes',
        'status',
        'is_winning',
        'bid_time',
        'retracted_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'max_bid_amount' => 'decimal:2',
        'is_auto_bid' => 'boolean',
        'is_winning' => 'boolean',
        'bid_time' => 'datetime',
        'retracted_at' => 'datetime'
    ];

    /**
     * Get auction
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    /**
     * Get bidder
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get shipping company (for delivery auctions)
     */
    public function company()
    {
        return $this->belongsTo(ShippingCompany::class, 'company_id');
    }

    /**
     * Mark as winning
     */
    public function markAsWinning()
    {
        // Remove winning status from other bids
        AuctionBid::where('auction_id', $this->auction_id)
            ->where('id', '!=', $this->id)
            ->update(['is_winning' => false, 'status' => 'outbid']);

        // Mark this as winning
        $this->is_winning = true;
        $this->status = 'winning';
        $this->save();
    }

    /**
     * Mark as outbid
     */
    public function markAsOutbid()
    {
        $this->is_winning = false;
        $this->status = 'outbid';
        $this->save();
    }

    /**
     * Retract bid
     */
    public function retract()
    {
        $this->status = 'retracted';
        $this->retracted_at = now();
        $this->save();
    }
}
