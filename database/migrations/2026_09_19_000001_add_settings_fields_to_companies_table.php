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
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'state')) {
                    $table->string('state')->nullable()->after('city');
                }
                if (!Schema::hasColumn('companies', 'financial_year')) {
                    $table->string('financial_year')->default('April - March')->after('currency_symbol');
                }
                if (!Schema::hasColumn('companies', 'financial_year_start')) {
                    $table->string('financial_year_start')->default('04-01')->after('financial_year');
                }
                if (!Schema::hasColumn('companies', 'nvidia_api_key')) {
                    $table->text('nvidia_api_key')->nullable()->after('tax_number');
                }
                if (!Schema::hasColumn('companies', 'nvidia_model')) {
                    $table->string('nvidia_model')->default('meta/llama-3.2-11b-vision-instruct')->after('nvidia_api_key');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $columns = ['state', 'financial_year', 'financial_year_start', 'nvidia_api_key', 'nvidia_model'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('companies', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
