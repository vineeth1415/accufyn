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
        // 1. Custom Ledgers (Chart of Accounts / General Ledger Accounts)
        if (!Schema::hasTable('ledgers')) {
            Schema::create('ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('code', 50)->nullable()->index();
                $table->enum('group', ['asset', 'liability', 'equity', 'income', 'expense'])->default('expense')->index();
                $table->decimal('opening_balance', 15, 2)->default(0.00);
                $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
                $table->decimal('current_balance', 15, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // 2. Ledger Entries (Debit & Credit Journal Transactions)
        if (!Schema::hasTable('ledger_entries')) {
            Schema::create('ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ledger_id')->index();
                $table->date('entry_date')->index();
                $table->enum('type', ['debit', 'credit'])->index();
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2)->nullable();
                $table->string('reference_number', 100)->nullable();
                $table->text('description')->nullable();
                $table->string('related_type', 50)->nullable();
                $table->unsignedBigInteger('related_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('ledger_id')->references('id')->on('ledgers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledgers');
    }
};
