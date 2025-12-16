<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Class Auction
 * @package App\Models
 */
class Auction extends Model
{
    use SoftDeletes;

    public $table = 'auctions';

    public $fillable = [
        'auction_number',
        'type',
        'user_id',
        'category_id',
        'title',
        'description',
        'product_id',
        'condition',
        'pickup_address',
        'pickup_city',
        'delivery_address',
        'delivery_city',
        'delivery_country',
        'package_weight',
        'package_type',
        'declared_value',
        'requires_insurance',
        'is_fragile',
        'starting_price',
        'reserve_price',
        'current_price',
        'buy_now_price',
        'min_bid_increment',
        'is_reverse_auction',
        'start_time',
        'end_time',
        'duration_minutes',
        'auto_extend',
        'extension_minutes',
        'shipping_method',
        'shipping_cost',
        'pickup_location',
        'delivery_speed',
        'status',
        'view_count',
        'bid_count',
        'watcher_count',
        'question_count',
        'winner_user_id',
        'winning_price',
        'won_at',
        'escrow_trx',
        'payment_status',
        'featured',
        'allow_questions',
        'private_auction',
        'verified_bidders_only',
        'specifications',
        'terms_and_conditions',
        'notes'
    ];

    protected $casts = [
        'requires_insurance' => 'boolean',
        'is_fragile' => 'boolean',
        'starting_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'buy_now_price' => 'decimal:2',
        'min_bid_increment' => 'decimal:2',
        'package_weight' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'winning_price' => 'decimal:2',
        'is_reverse_auction' => 'boolean',
        'auto_extend' => 'boolean',
        'featured' => 'boolean',
        'allow_questions' => 'boolean',
        'private_auction' => 'boolean',
        'verified_bidders_only' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'won_at' => 'datetime',
        'specifications' => 'array'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Generate unique auction number
     */
    public static function generateAuctionNumber()
    {
        do {
            $number = 'AU-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('auction_number', $number)->exists());

        return $number;
    }

    /**
     * Get auction seller/creator
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get auction category
     */
    public function category()
    {
        return $this->belongsTo(AuctionCategory::class, 'category_id');
    }

    /**
     * Get linked product (if any)
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    /**
     * Get winner
     */
    public function winner()
    {
        return $this->belongsTo(\App\Models\User::class, 'winner_user_id');
    }

    /**
     * Get auction images
     */
    public function images()
    {
        return $this->hasMany(AuctionImage::class, 'auction_id')->orderBy('order');
    }

    /**
     * Get primary image
     */
    public function primaryImage()
    {
        return $this->hasOne(AuctionImage::class, 'auction_id')->where('is_primary', true);
    }

    /**
     * Get auction bids
     */
    public function bids()
    {
        return $this->hasMany(AuctionBid::class, 'auction_id')->orderBy('bid_time', 'desc');
    }

    /**
     * Get winning bid
     */
    public function winningBid()
    {
        return $this->hasOne(AuctionBid::class, 'auction_id')
            ->where('is_winning', true)
            ->orderBy('bid_amount', $this->is_reverse_auction ? 'asc' : 'desc');
    }

    /**
     * Get auction questions
     */
    public function questions()
    {
        return $this->hasMany(AuctionQuestion::class, 'auction_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get auction watchers
     */
    public function watchers()
    {
        return $this->hasMany(AuctionWatcher::class, 'auction_id');
    }

    /**
     * Get auto-bids
     */
    public function autoBids()
    {
        return $this->hasMany(AuctionAutoBid::class, 'auction_id')->where('active', true);
    }

    /**
     * Get activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(AuctionActivityLog::class, 'auction_id')->orderBy('created_at', 'desc');
    }

    /**
     * Check if auction is active
     */
    public function isActive()
    {
        return $this->status === 'active'
            && $this->start_time <= now()
            && $this->end_time > now();
    }

    /**
     * Check if auction has ended
     */
    public function hasEnded()
    {
        return $this->end_time < now() || $this->status === 'ended';
    }

    /**
     * Check if auction can accept bids
     */
    public function canAcceptBids()
    {
        return $this->isActive() && $this->status === 'active';
    }

    /**
     * Get time remaining
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->hasEnded()) {
            return null;
        }

        return Carbon::now()->diffForHumans($this->end_time, true);
    }

    /**
     * Check if user is watching
     */
    public function isWatchedBy($userId)
    {
        return $this->watchers()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user has bid
     */
    public function hasBidFrom($userId)
    {
        return $this->bids()->where('user_id', $userId)->exists();
    }

    /**
     * Get highest bid for normal auction or lowest for reverse
     */
    public function getCurrentBidAttribute()
    {
        if ($this->is_reverse_auction) {
            return $this->bids()->where('status', 'active')->min('bid_amount');
        }

        return $this->bids()->where('status', 'active')->max('bid_amount');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Check if reserve price is met
     */
    public function isReservePriceMet()
    {
        if (!$this->reserve_price) {
            return true;
        }

        if ($this->is_reverse_auction) {
            return $this->current_price <= $this->reserve_price;
        }

        return $this->current_price >= $this->reserve_price;
    }

    /**
     * Extend auction time
     */
    public function extendTime($minutes = null)
    {
        $minutes = $minutes ?? $this->extension_minutes;
        $this->end_time = $this->end_time->addMinutes($minutes);
        $this->save();
    }

    /**
     * Complete auction and determine winner
     */
    public function complete()
    {
        $winningBid = $this->winningBid;

        if ($winningBid) {
            $this->winner_user_id = $winningBid->user_id;
            $this->winning_price = $winningBid->bid_amount;
            $this->won_at = now();
            $this->status = 'completed';

            // Update bid status
            $winningBid->update(['status' => 'won']);

            // Update other bids
            $this->bids()->where('id', '!=', $winningBid->id)
                ->update(['status' => 'lost']);
        } else {
            $this->status = 'expired';
        }

        $this->save();
    }
}
