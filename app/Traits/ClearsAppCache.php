<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * ClearsAppCache
 *
 * Attach to any Eloquent model to automatically flush related
 * dashboard / KPI caches whenever a record is saved or deleted.
 *
 * Usage:
 *   use App\Traits\ClearsAppCache;
 *   class Invoice extends Model { use ClearsAppCache; }
 */
trait ClearsAppCache
{
    public static function bootClearsAppCache(): void
    {
        static::saved(fn()   => static::flushRelatedCaches());
        static::deleted(fn() => static::flushRelatedCaches());
    }

    protected static function flushRelatedCaches(): void
    {
        $keys = [
            'kpi_total_cash',
            'kpi_receivables',
            'kpi_payables',
            'kpi_income_expense',
            'chart_cashflow_6m',
            'chart_expense_categories',
            'recent_invoices',
            'recent_transactions',
            'bank_accounts_list',
            'company_settings',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
