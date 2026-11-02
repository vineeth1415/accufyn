<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // invoices: status filter, date sorting, due_amount aggregates
        Schema::table('invoices', function (Blueprint $table) {
            if (!$this->indexExists('invoices', 'invoices_status_index')) {
                $table->index('status', 'invoices_status_index');
            }
            if (!$this->indexExists('invoices', 'invoices_due_amount_index')) {
                $table->index('due_amount', 'invoices_due_amount_index');
            }
            if (!$this->indexExists('invoices', 'invoices_created_at_index')) {
                $table->index('created_at', 'invoices_created_at_index');
            }
            if (!$this->indexExists('invoices', 'invoices_customer_status_index')) {
                $table->index(['customer_id', 'status'], 'invoices_customer_status_index');
            }
        });

        // bills: status filter, date sorting
        Schema::table('bills', function (Blueprint $table) {
            if (!$this->indexExists('bills', 'bills_status_index')) {
                $table->index('status', 'bills_status_index');
            }
            if (!$this->indexExists('bills', 'bills_due_amount_index')) {
                $table->index('due_amount', 'bills_due_amount_index');
            }
            if (!$this->indexExists('bills', 'bills_created_at_index')) {
                $table->index('created_at', 'bills_created_at_index');
            }
            if (!$this->indexExists('bills', 'bills_vendor_status_index')) {
                $table->index(['vendor_id', 'status'], 'bills_vendor_status_index');
            }
        });

        // transactions: type + date (heavy dashboard queries)
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->indexExists('transactions', 'transactions_type_index')) {
                $table->index('type', 'transactions_type_index');
            }
            if (!$this->indexExists('transactions', 'transactions_date_index')) {
                $table->index('transaction_date', 'transactions_date_index');
            }
            if (!$this->indexExists('transactions', 'transactions_type_date_index')) {
                $table->index(['type', 'transaction_date'], 'transactions_type_date_index');
            }
            if (!$this->indexExists('transactions', 'transactions_created_at_index')) {
                $table->index('created_at', 'transactions_created_at_index');
            }
        });

        // customers: is_active filter
        Schema::table('customers', function (Blueprint $table) {
            if (!$this->indexExists('customers', 'customers_is_active_index')) {
                $table->index('is_active', 'customers_is_active_index');
            }
        });

        // vendors: is_active filter
        Schema::table('vendors', function (Blueprint $table) {
            if (!$this->indexExists('vendors', 'vendors_is_active_index')) {
                $table->index('is_active', 'vendors_is_active_index');
            }
        });

        // items: is_active filter
        Schema::table('items', function (Blueprint $table) {
            if (!$this->indexExists('items', 'items_is_active_index')) {
                $table->index('is_active', 'items_is_active_index');
            }
        });

        // categories: type filter
        Schema::table('categories', function (Blueprint $table) {
            if (!$this->indexExists('categories', 'categories_type_index')) {
                $table->index('type', 'categories_type_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndexIfExists('invoices_status_index');
            $table->dropIndexIfExists('invoices_due_amount_index');
            $table->dropIndexIfExists('invoices_created_at_index');
            $table->dropIndexIfExists('invoices_customer_status_index');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndexIfExists('bills_status_index');
            $table->dropIndexIfExists('bills_due_amount_index');
            $table->dropIndexIfExists('bills_created_at_index');
            $table->dropIndexIfExists('bills_vendor_status_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndexIfExists('transactions_type_index');
            $table->dropIndexIfExists('transactions_date_index');
            $table->dropIndexIfExists('transactions_type_date_index');
            $table->dropIndexIfExists('transactions_created_at_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndexIfExists('customers_is_active_index');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndexIfExists('vendors_is_active_index');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndexIfExists('items_is_active_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndexIfExists('categories_type_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($indexes);
    }
};
