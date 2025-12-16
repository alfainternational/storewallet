<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionActivityLog
 * @package App\Models
 */
class AuctionActivityLog extends Model
{
    public $table = 'auction_activity_logs';

    public $fillable = [
        'auction_id',
        'user_id',
        'action',
        'description',
        'metadata',
        'ip_address'
    ];

    protected $casts = [
        'metadata' => 'array'
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
     * Log activity
     */
    public static function logActivity($auctionId, $action, $description, $metadata = null, $userId = null)
    {
        return self::create([
            'auction_id' => $auctionId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip()
        ]);
    }
}
