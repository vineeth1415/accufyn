<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'name', 'sku', 'description', 'category_id', 
        'sale_price', 'purchase_price', 'tax_id', 'unit', 'is_active'
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
