<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionWatcher
 * @package App\Models
 */
class AuctionWatcher extends Model
{
    public $table = 'auction_watchers';

    public $fillable = [
        'auction_id',
        'user_id',
        'notify_on_bid',
        'notify_on_outbid',
        'notify_on_ending',
        'notify_on_price_drop'
    ];

    protected $casts = [
        'notify_on_bid' => 'boolean',
        'notify_on_outbid' => 'boolean',
        'notify_on_ending' => 'boolean',
        'notify_on_price_drop' => 'boolean'
    ];

    /**
     * Get auction
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    /**
     * Get watcher user
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
