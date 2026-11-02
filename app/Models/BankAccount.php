<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'name', 'type', 'account_number', 'bank_name', 
        'ifsc_code', 'branch_name', 'account_type', 'upi_id', 'bank_address',
        'currency', 'opening_balance', 'current_balance', 'is_default', 'status'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function getAccountNameAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    public function setAccountNameAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
