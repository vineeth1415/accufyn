<?php

namespace App\Models;

use App\Traits\ClearsAppCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use ClearsAppCache;
    protected $fillable = [
        'bill_number', 'vendor_id', 'bill_date', 'due_date',
        'subtotal', 'tax_total', 'discount_total', 'total',
        'paid_amount', 'due_amount', 'status', 'notes'
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->total;
    }

    public function getTaxAmountAttribute()
    {
        return $this->tax_total;
    }
}
