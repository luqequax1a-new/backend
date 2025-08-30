<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'unit_id',
        'store_id',
        'price',
        'tax_rate',
        'stock_qty',
        'min_qty',
        'max_qty',
        'is_active',
        'images',
        'brand_id',
        'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_qty' => 'decimal:3',
        'min_qty' => 'decimal:3',
        'max_qty' => 'decimal:3',
        'is_active' => 'boolean',
        'images' => 'array'
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
    
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
