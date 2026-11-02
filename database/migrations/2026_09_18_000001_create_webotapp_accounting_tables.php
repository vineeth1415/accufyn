<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Company Information & Settings
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('WebotApp Enterprise');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('currency_code', 10)->default('INR');
            $table->string('currency_symbol', 10)->default('₹');
            $table->string('financial_year')->default('April - March');
            $table->string('financial_year_start')->default('04-01');
            $table->string('tax_number')->nullable();
            $table->text('nvidia_api_key')->nullable();
            $table->string('nvidia_model')->default('meta/llama-3.2-11b-vision-instruct');
            $table->string('logo_path')->nullable();
            $table->string('theme_color', 30)->default('emerald');
            $table->timestamps();
        });
        }

        // 2. Customers (Receivables)
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // 3. Vendors (Payables)
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // 4. Categories (Income, Expense, Items)
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['income', 'expense', 'item'])->default('expense');
            $table->string('color', 20)->default('#10b981');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // 5. Taxes
        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 8, 2)->default(0.00);
            $table->enum('type', ['normal', 'inclusive', 'compound'])->default('normal');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // 6. Products & Services (Items)
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('sale_price', 15, 2)->default(0.00);
            $table->decimal('purchase_price', 15, 2)->default(0.00);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->string('unit')->default('unit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        }

        // 7. Bank & Cash Accounts
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['bank', 'cash', 'card'])->default('bank');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code', 30)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->string('account_type', 50)->nullable();
            $table->string('upi_id', 100)->nullable();
            $table->text('bank_address')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('is_default')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
        }

        // 8. Sales Invoices
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_total', 15, 2)->default(0.00);
            $table->decimal('discount_total', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('due_amount', 15, 2)->default(0.00);
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('public_token', 64)->unique();
            $table->timestamps();
        });
        }

        // 9. Invoice Items
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 8, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->timestamps();
        });
        }

        // 10. Vendor Bills (Purchases)
        if (!Schema::hasTable('bills')) {
            Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_total', 15, 2)->default(0.00);
            $table->decimal('discount_total', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('due_amount', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'received', 'partial', 'paid', 'overdue'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        }

        // 11. Bill Items
        if (!Schema::hasTable('bill_items')) {
            Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->decimal('tax_rate', 8, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2)->default(0.00);
            $table->timestamps();
        });
        }

        // 12. Universal Financial Transactions & Banking Ledger
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->foreignId('to_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('items');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('companies');
    }
};
