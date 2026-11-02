<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'openrouter_api_key')) {
                $table->text('openrouter_api_key')->nullable()->after('nvidia_model');
            }
            if (!Schema::hasColumn('companies', 'openrouter_model')) {
                $table->string('openrouter_model', 150)->default('meta-llama/llama-3.2-3b-instruct:free')->after('openrouter_api_key');
            }
            if (!Schema::hasColumn('companies', 'openrouter_temperature')) {
                $table->decimal('openrouter_temperature', 3, 2)->default(0.20)->after('openrouter_model');
            }
            if (!Schema::hasColumn('companies', 'openrouter_max_tokens')) {
                $table->integer('openrouter_max_tokens')->default(800)->after('openrouter_temperature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['openrouter_api_key', 'openrouter_model', 'openrouter_temperature', 'openrouter_max_tokens']);
        });
    }
};
