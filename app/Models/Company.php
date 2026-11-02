<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'city', 'state', 'country',
        'currency_code', 'currency_symbol', 'tax_number', 'logo_path', 'theme_color',
        'financial_year_start', 'financial_year',
        'nvidia_api_key', 'nvidia_model',
        'openrouter_api_key', 'openrouter_model', 'openrouter_temperature', 'openrouter_max_tokens',
    ];

    /**
     * Alias for currency_code
     */
    public function getCurrencyAttribute()
    {
        return $this->currency_code;
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency_code'] = $value;
    }
}
