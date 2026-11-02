<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Category;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;

class SettingController extends Controller
{
    /**
     * Ensure any missing columns exist on the companies table automatically.
     */
    private function ensureCompanyTableSchema(): void
    {
        try {
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
                    if (!Schema::hasColumn('companies', 'logo_path')) {
                        $table->string('logo_path')->nullable()->after('name');
                    }
                    if (!Schema::hasColumn('companies', 'theme_color')) {
                        $table->string('theme_color', 30)->default('emerald')->after('logo_path');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-heal companies table columns warning: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $this->ensureCompanyTableSchema();

        $company = Company::first() ?? new Company([
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'financial_year' => 'April - March',
            'financial_year_start' => '04-01'
        ]);
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $taxes = Tax::orderBy('name')->get();

        return view('settings.index', compact('company', 'categories', 'taxes'));
    }

    public function updateCompany(Request $request)
    {
        $this->ensureCompanyTableSchema();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'currency_code' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'required|string|max:10',
        ]);

        try {
            $company = Company::first();
            if (!$company) {
                $company = new Company();
            }

            $currencyCode = $request->currency_code ?: ($request->currency ?: 'INR');

            $data = $request->only([
                'name', 'email', 'phone', 'address', 'city', 'state', 'country',
                'tax_number', 'currency_symbol', 'financial_year_start', 'financial_year'
            ]);

            // Filter data by columns that exist in the database table to prevent SQL 1054 crashes
            $columns = Schema::hasTable('companies') ? Schema::getColumnListing('companies') : [];
            if (!empty($columns)) {
                $filteredData = array_intersect_key($data, array_flip($columns));
            } else {
                $filteredData = $data;
            }

            $company->fill($filteredData);

            if (empty($columns) || in_array('currency_code', $columns)) {
                $company->currency_code = $currencyCode;
            }

            if (empty($columns) || in_array('financial_year', $columns)) {
                if ($request->filled('financial_year')) {
                    $company->financial_year = $request->financial_year;
                    if (empty($columns) || in_array('financial_year_start', $columns)) {
                        if ($request->financial_year === 'April - March') {
                            $company->financial_year_start = '04-01';
                        } elseif ($request->financial_year === 'January - December') {
                            $company->financial_year_start = '01-01';
                        }
                    }
                }
            }

            if ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
                ]);
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $dest = public_path('uploads/company');
                if (!file_exists($dest)) {
                    @mkdir($dest, 0777, true);
                }
                $file->move($dest, $filename);
                @chmod($dest . '/' . $filename, 0666);

                // Mirror to base_path('uploads/company') so Apache serves directly from root if requested
                $baseDest = base_path('uploads/company');
                if (!file_exists($baseDest)) {
                    @mkdir($baseDest, 0777, true);
                }
                @copy($dest . '/' . $filename, $baseDest . '/' . $filename);
                @chmod($baseDest . '/' . $filename, 0666);

                $company->logo_path = 'uploads/company/' . $filename;
            } elseif ($request->boolean('remove_logo')) {
                $company->logo_path = null;
            }

            if ($request->filled('theme_color')) {
                $company->theme_color = $request->theme_color;
            }

            $company->save();

            return back()->with('success', 'Company and financial settings updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Settings update error: ' . $e->getMessage());
            return back()->with('error', 'Could not save settings: ' . $e->getMessage());
        }
    }

    public function updateAi(Request $request)
    {
        $this->ensureCompanyTableSchema();

        $request->validate([
            'nvidia_api_key' => 'nullable|string',
            'nvidia_model' => 'nullable|string|max:100',
        ]);

        try {
            $company = Company::first();
            if (!$company) {
                $company = new Company();
            }

            $columns = Schema::hasTable('companies') ? Schema::getColumnListing('companies') : [];
            if (empty($columns) || in_array('nvidia_api_key', $columns)) {
                $company->nvidia_api_key = trim($request->input('nvidia_api_key', ''));
            }
            if (empty($columns) || in_array('nvidia_model', $columns)) {
                $company->nvidia_model = $request->input('nvidia_model') ?: 'meta/llama-3.2-11b-vision-instruct';
            }
            $company->save();

            return back()->with('success', 'AI Assistant & NVIDIA NIM settings updated successfully.');
        } catch (\Throwable $e) {
            Log::error('AI settings update error: ' . $e->getMessage());
            return back()->with('error', 'Could not save AI settings: ' . $e->getMessage());
        }
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense,item,other',
            'color' => 'nullable|string|max:20',
        ]);

        try {
            Category::create([
                'name' => $request->name,
                'type' => in_array($request->type, ['income', 'expense', 'item']) ? $request->type : 'expense',
                'color' => $request->color ?? '#10b981',
                'is_active' => true,
            ]);

            return back()->with('success', 'Category added successfully.');
        } catch (\Throwable $e) {
            Log::error('Category add error: ' . $e->getMessage());
            return back()->with('error', 'Could not add category: ' . $e->getMessage());
        }
    }

    public function storeTax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'nullable|string|max:20',
        ]);

        try {
            Tax::create([
                'name' => $request->name,
                'rate' => $request->rate,
                'type' => in_array($request->type, ['normal', 'inclusive', 'compound']) ? $request->type : 'normal',
                'is_active' => true,
            ]);

            return back()->with('success', 'Tax rate added successfully.');
        } catch (\Throwable $e) {
            Log::error('Tax rate add error: ' . $e->getMessage());
            return back()->with('error', 'Could not add tax rate: ' . $e->getMessage());
        }
    }

    /**
     * Update Administrator Account Password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user() ?? User::where('role', 'ADMIN')->first() ?? User::first();
        if (!$user) {
            return back()->with('error', 'Administrator user not found.');
        }

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'The current password you entered is incorrect.');
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return back()->with('success', 'Administrator password updated successfully!');
    }
}

