<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankingController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Installer Routes (Redirect to Login)
|--------------------------------------------------------------------------
*/
Route::any('/install/{any?}', function () {
    return redirect()->route('login');
})->where('any', '.*')->name('install.index');

/*
|--------------------------------------------------------------------------
| Public Uploads Fallback (Ensures logos & media serve under subdirectories)
|--------------------------------------------------------------------------
*/
Route::get('/uploads/{path}', function ($path) {
    $filePath = public_path('uploads/' . $path);
    if (!file_exists($filePath)) {
        $filePath = base_path('uploads/' . $path);
    }
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*')->name('uploads.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Public Invoice View (Client Portal)
|--------------------------------------------------------------------------
*/
Route::get('/invoices/public/{token}', [InvoiceController::class, 'publicShow'])->name('invoices.public');

Route::get('/', function () {
    if (Auth::check()) {
        if (strtoupper(Auth::user()->role ?? '') === 'AUDITOR') {
            return redirect()->route('reports.profit_loss');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/admin', function () {
    if (Auth::check()) {
        if (strtoupper(Auth::user()->role ?? '') === 'AUDITOR') {
            return redirect()->route('reports.profit_loss');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Accounting Application Routes with RBAC
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard (Admin, Accountant, Staff)
    Route::middleware(['role:admin,accountant,staff'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // 2. Sales & Revenue: Invoices & Customers (Admin, Accountant, Staff)
    Route::middleware(['role:admin,accountant,staff'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{id}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
        Route::post('/invoices/{id}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
        Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.sendEmail');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // 3. Purchases & Expenses: Bills & Vendors (Admin, Accountant, Staff)
    Route::middleware(['role:admin,accountant,staff'])->group(function () {
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{id}', [BillController::class, 'show'])->name('bills.show');
        Route::get('/bills/{id}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('/bills/{id}', [BillController::class, 'update'])->name('bills.update');
        Route::get('/bills/{id}/print', [BillController::class, 'print'])->name('bills.print');
        Route::delete('/bills/{id}', [BillController::class, 'destroy'])->name('bills.destroy');
        Route::post('/bills/{id}/payment', [BillController::class, 'recordPayment'])->name('bills.payment');

        Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('/vendors/{id}', [VendorController::class, 'show'])->name('vendors.show');
        Route::get('/vendors/{id}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('/vendors/{id}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('/vendors/{id}', [VendorController::class, 'destroy'])->name('vendors.destroy');

        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

    // 4. Banking & Cash (Admin, Accountant)
    Route::middleware(['role:admin,accountant'])->group(function () {
        Route::get('/banking', [BankingController::class, 'index'])->name('banking.index');
        Route::post('/banking', [BankingController::class, 'store'])->name('banking.store');
        Route::put('/banking/accounts/{id}', [BankingController::class, 'updateAccount'])->name('banking.accounts.update');
        Route::delete('/banking/accounts/{id}', [BankingController::class, 'destroyAccount'])->name('banking.accounts.destroy');
        Route::get('/banking/transfer', [BankingController::class, 'transferForm'])->name('banking.transfer');
        Route::post('/banking/transfer', [BankingController::class, 'transfer'])->name('banking.transfer.post');
        Route::get('/banking/transactions', [BankingController::class, 'transactions'])->name('banking.transactions');
        Route::delete('/banking/transactions/{id}', [BankingController::class, 'destroyTransaction'])->name('banking.transactions.destroy');
    });

    // 5. Custom Ledgers & Chart of Accounts
    // Auditor has view & export privilege; Admin and Accountant can create/edit/post
    Route::middleware(['role:admin,accountant,auditor'])->group(function () {
        Route::get('/ledgers', [LedgerController::class, 'index'])->name('ledgers.index');
        Route::get('/ledgers/{id}', [LedgerController::class, 'show'])->name('ledgers.show');
        Route::get('/ledgers/{id}/export', [LedgerController::class, 'export'])->name('ledgers.export');
    });
    Route::middleware(['role:admin,accountant'])->group(function () {
        Route::post('/ledgers', [LedgerController::class, 'store'])->name('ledgers.store');
        Route::put('/ledgers/{id}', [LedgerController::class, 'update'])->name('ledgers.update');
        Route::delete('/ledgers/{id}', [LedgerController::class, 'destroy'])->name('ledgers.destroy');
        Route::post('/ledgers/{id}/entries', [LedgerController::class, 'storeEntry'])->name('ledgers.entries.store');
        Route::delete('/ledgers/{id}/entries/{entryId}', [LedgerController::class, 'destroyEntry'])->name('ledgers.entries.destroy');
    });

    // 6. Financial Reports & Excel Filing Exports (Admin, Accountant, Auditor)
    // Especially: Auditor has privilege to view & export financial reports as Excel for filing
    Route::middleware(['role:admin,accountant,auditor'])->group(function () {
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit_loss');
        Route::get('/reports/profit-loss/export', [ReportController::class, 'exportProfitLoss'])->name('reports.profit_loss.export');

        Route::get('/reports/income-expense', [ReportController::class, 'incomeExpense'])->name('reports.income_expense');
        Route::get('/reports/income-expense/export', [ReportController::class, 'exportIncomeExpense'])->name('reports.income_expense.export');

        Route::get('/reports/tax-summary', [ReportController::class, 'taxSummary'])->name('reports.tax_summary');
        Route::get('/reports/tax-summary/export', [ReportController::class, 'exportTaxSummary'])->name('reports.tax_summary.export');

        Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial_balance');
        Route::get('/reports/trial-balance/export', [ReportController::class, 'exportTrialBalance'])->name('reports.trial_balance.export');
    });

    // 7. User Management (Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // 8. Settings & Updates (Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.company');
        Route::post('/settings/categories', [SettingController::class, 'storeCategory'])->name('settings.categories');
        Route::post('/settings/taxes', [SettingController::class, 'storeTax'])->name('settings.taxes');
        Route::post('/settings/ai', [SettingController::class, 'updateAi'])->name('settings.ai');
        Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');

        Route::get('/updates', [UpdateController::class, 'index'])->name('updates.index');
        Route::get('/updates/check', [UpdateController::class, 'check'])->name('updates.check');
        Route::post('/updates/apply', [UpdateController::class, 'apply'])->name('updates.apply');
    });

    // 9. AI Assistant
    Route::middleware(['role:admin,accountant,auditor'])->group(function () {
        Route::post('/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/ai/test-connection', [AiChatController::class, 'testConnection'])->name('ai.test');
    });
});
