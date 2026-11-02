<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Models\Company;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auto-heal storage & cache directories and permissions
        $storageDirs = [
            storage_path('framework/views'),
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        foreach ($storageDirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            if (!is_writable($dir)) {
                @chmod($dir, 0777);
            }
        }

        // Before installation, force session & cache to file driver so web installer never tries to connect to an unconfigured database
        if (!file_exists(storage_path('installed'))) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (file_exists(storage_path('installed'))) {
            try {
                // Auto-heal missing database columns seamlessly
                if (\Illuminate\Support\Facades\Schema::hasTable('bank_accounts')) {
                    \Illuminate\Support\Facades\Schema::table('bank_accounts', function (\Illuminate\Database\Schema\Blueprint $table) {
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'status')) {
                            $table->string('status', 20)->default('active')->after('is_default');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'ifsc_code')) {
                            $table->string('ifsc_code', 30)->nullable()->after('bank_name');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'branch_name')) {
                            $table->string('branch_name', 100)->nullable()->after('ifsc_code');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'account_type')) {
                            $table->string('account_type', 50)->nullable()->after('branch_name');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'upi_id')) {
                            $table->string('upi_id', 100)->nullable()->after('account_type');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'bank_address')) {
                            $table->text('bank_address')->nullable()->after('upi_id');
                        }
                    });
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('companies')) {
                    \Illuminate\Support\Facades\Schema::table('companies', function (\Illuminate\Database\Schema\Blueprint $table) {
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('companies', 'logo_path')) {
                            $table->string('logo_path')->nullable()->after('name');
                        }
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('companies', 'theme_color')) {
                            $table->string('theme_color', 30)->default('emerald')->after('logo_path');
                        }
                    });
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                    try {
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `invoices` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'pending'");
                    } catch (\Throwable $e) {
                        // Safe ignore on SQLite or if already modified
                    }
                }

                View::composer('*', function ($view) {
                    $company = null;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('companies')) {
                            $company = Company::first();
                            if ($company && ($company->currency_code === 'USD' || empty($company->currency_symbol) || $company->currency_symbol === '$')) {
                                try {
                                    $company->update([
                                        'currency_code' => 'INR',
                                        'currency_symbol' => '₹',
                                    ]);
                                } catch (\Throwable $e) {}
                            }
                        }
                    } catch (\Throwable $e) {
                        $company = null;
                    }

                    if (!$company) {
                        $company = new Company([
                            'name' => 'WebotApp Accounting',
                            'currency_code' => 'INR',
                            'currency_symbol' => '₹',
                            'financial_year' => 'April - March',
                            'financial_year_start' => '04-01',
                            'theme_color' => 'emerald'
                        ]);
                    }

                    $view->with('company', $company);
                    $view->with('currencySymbol', $company->currency_symbol ?? '₹');
                });

                Blade::directive('money', function ($expression) {
                    return "<?php echo (\$currencySymbol ?? '₹') . number_format($expression, 2); ?>";
                });
            } catch (\Throwable $e) {
                // Ignore during initial migrations or connection establishment
            }
        }
    }
}
