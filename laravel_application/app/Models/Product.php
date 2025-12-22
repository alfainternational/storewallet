<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'merchant_id',
        'category_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'original_price',
        'discount_percentage',
        'stock',
        'sku',
        'weight',
        'dimensions',
        'is_active',
        'is_featured',
        'views_count',
        'sales_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_percentage' => 'integer',
        'stock' => 'integer',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'sales_count' => 'integer',
    ];

    // Relationships
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getMainImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first()?->image_url
            ?? $this->images()->first()?->image_url;
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getFinalPriceAttribute()
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock <= 0;
    }

    public function getIsNewAttribute()
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    // Methods
    public function decrementStock($quantity)
    {
        $this->decrement('stock', $quantity);
    }

    public function incrementStock($quantity)
    {
        $this->increment('stock', $quantity);
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementSales()
    {
        $this->increment('sales_count');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_ar', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('description_ar', 'like', "%{$search}%");
        });
    }
}
