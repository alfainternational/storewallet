<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionImage
 * @package App\Models
 */
class AuctionImage extends Model
{
    public $table = 'auction_images';

    public $fillable = [
        'auction_id',
        'image_path',
        'thumbnail_path',
        'is_primary',
        'order',
        'caption'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get auction
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    /**
     * Get full image URL
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        return $this->image_url;
    }
}
