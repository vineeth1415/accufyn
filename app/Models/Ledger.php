<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'group',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->orderBy('entry_date', 'asc')->orderBy('id', 'asc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this ledger account normally has a Debit balance.
     * Assets and Expenses are normally Debit.
     * Liabilities, Equity, and Income are normally Credit.
     */
    public function isDebitNormal(): bool
    {
        return in_array(strtolower($this->group), ['asset', 'expense']);
    }

    /**
     * Recalculates and updates the ledger's running balance based on opening balance and all entries.
     */
    public function recalculateBalance(): float
    {
        $balance = (float) $this->opening_balance;
        if (!$this->isDebitNormal() && $this->opening_balance_type === 'debit') {
            $balance = -$balance;
        } elseif ($this->isDebitNormal() && $this->opening_balance_type === 'credit') {
            $balance = -$balance;
        }

        $entries = $this->entries()->get();

        foreach ($entries as $entry) {
            $amt = (float) $entry->amount;
            if ($this->isDebitNormal()) {
                // Debit increases, Credit decreases
                $balance += ($entry->type === 'debit' ? $amt : -$amt);
            } else {
                // Credit increases, Debit decreases
                $balance += ($entry->type === 'credit' ? $amt : -$amt);
            }
            $entry->balance_after = $balance;
            $entry->saveQuietly();
        }

        $this->current_balance = $balance;
        $this->saveQuietly();

        return $balance;
    }

    /**
     * Group label with badge styling class
     */
    public function getGroupBadgeAttribute(): array
    {
        return match (strtolower($this->group)) {
            'asset'     => ['label' => 'Asset',     'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'liability' => ['label' => 'Liability', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'equity'    => ['label' => 'Equity',    'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'income'    => ['label' => 'Income',    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'expense'   => ['label' => 'Expense',   'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
            default     => ['label' => ucfirst($this->group), 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
        };
    }
}
