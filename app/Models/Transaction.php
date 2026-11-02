<?php

namespace App\Models;

use App\Traits\ClearsAppCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use ClearsAppCache;
    protected $fillable = [
        'type', 'bank_account_id', 'to_bank_account_id', 'customer_id',
        'vendor_id', 'invoice_id', 'bill_id', 'category_id',
        'amount', 'payment_method', 'reference_number', 'transaction_date', 'description'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function toBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_account_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
