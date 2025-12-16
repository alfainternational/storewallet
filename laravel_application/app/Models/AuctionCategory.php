<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionCategory
 * @package App\Models
 */
class AuctionCategory extends Model
{
    public $table = 'auction_categories';

    public $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'icon',
        'color',
        'parent_id',
        'order',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get parent category
     */
    public function parent()
    {
        return $this->belongsTo(AuctionCategory::class, 'parent_id');
    }

    /**
     * Get child categories
     */
    public function children()
    {
        return $this->hasMany(AuctionCategory::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get auctions in this category
     */
    public function auctions()
    {
        return $this->hasMany(Auction::class, 'category_id');
    }

    /**
     * Get active auctions count
     */
    public function getActiveAuctionsCountAttribute()
    {
        return $this->auctions()->where('status', 'active')->count();
    }
}
