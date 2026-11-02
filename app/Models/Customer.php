<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company_name', 'tax_number', 
        'address', 'city', 'country', 'currency', 'balance', 'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Outstanding / Receivable balance accessor
     */
    public function getOutstandingBalanceAttribute()
    {
        $dueInvoices = (float) $this->invoices()->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->sum('due_amount');
        if ($dueInvoices > 0) {
            return $dueInvoices;
        }
        return (float) ($this->balance ?? 0);
    }
}
