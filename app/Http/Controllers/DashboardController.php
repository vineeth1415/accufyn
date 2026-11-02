<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $company = Cache::remember('company_settings', 300, fn() =>
            Company::first() ?? new Company(['name' => 'WebotApp Accounting', 'currency_symbol' => '₹', 'currency_code' => 'INR'])
        );

        // KPI 1: Bank & Cash Balances (short TTL — balances change often)
        $totalCash   = Cache::remember('kpi_total_cash', 60, fn() => BankAccount::sum('current_balance'));
        $bankAccounts = Cache::remember('bank_accounts_list', 60, fn() =>
            BankAccount::orderByDesc('is_default')->get()
        );

        // KPI 2 & 3: Receivables / Payables (1-min cache)
        $totalReceivables = Cache::remember('kpi_receivables', 60, fn() =>
            Invoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->sum('due_amount')
        );
        $totalPayables = Cache::remember('kpi_payables', 60, fn() =>
            Bill::whereIn('status', ['draft', 'received', 'partial', 'overdue'])->sum('due_amount')
        );

        // KPI 4: Net Profit — single DB round-trip using conditional aggregation
        [$totalIncome, $totalExpense] = Cache::remember('kpi_income_expense', 120, function () {
            $row = DB::selectOne("
                SELECT
                    COALESCE(SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END), 0) AS income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
                FROM transactions
            ");
            return [(float)$row->income, (float)$row->expense];
        });
        $netProfit = $totalIncome - $totalExpense;

        // Chart 1: Cash Flow — ONE query for all 6 months instead of 12 separate queries
        [$months, $incomeData, $expenseData] = Cache::remember('chart_cashflow_6m', 120, function () {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();

            $rows = DB::select("
                SELECT
                    YEAR(transaction_date)  AS yr,
                    MONTH(transaction_date) AS mo,
                    COALESCE(SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END), 0) AS income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
                FROM transactions
                WHERE transaction_date >= ?
                GROUP BY YEAR(transaction_date), MONTH(transaction_date)
                ORDER BY yr, mo
            ", [$startDate]);

            // Index results by year-month for quick lookup
            $map = [];
            foreach ($rows as $r) {
                $map["{$r->yr}-{$r->mo}"] = $r;
            }

            $months = $incomeData = $expenseData = [];
            for ($i = 5; $i >= 0; $i--) {
                $d   = Carbon::now()->subMonths($i);
                $key = "{$d->year}-{$d->month}";
                $months[]     = $d->format('M Y');
                $incomeData[]  = (float)($map[$key]->income  ?? 0);
                $expenseData[] = (float)($map[$key]->expense ?? 0);
            }

            return [$months, $incomeData, $expenseData];
        });

        // Chart 2: Expenses by Category — 2-min cache
        [$categoryLabels, $categorySeries, $categoryColors] = Cache::remember('chart_expense_categories', 120, function () {
            $rows = DB::select("
                SELECT c.name, c.color, COALESCE(SUM(t.amount), 0) AS total_amount
                FROM transactions t
                INNER JOIN categories c ON c.id = t.category_id
                WHERE t.type = 'expense'
                GROUP BY c.id, c.name, c.color
                ORDER BY total_amount DESC
            ");

            $labels = $series = $colors = [];
            foreach ($rows as $r) {
                $labels[] = $r->name ?? 'Uncategorized';
                $series[] = (float)$r->total_amount;
                $colors[] = $r->color ?? '#10b981';
            }
            return [$labels, $series, $colors];
        });

        // Recent Invoices & Transactions (short cache — 30 s)
        $recentInvoices = Cache::remember('recent_invoices', 30, fn() =>
            Invoice::with('customer')->latest()->take(5)->get()
        );

        $recentTransactions = Cache::remember('recent_transactions', 30, fn() =>
            Transaction::with(['bankAccount', 'customer', 'vendor', 'category'])
                ->latest('transaction_date')
                ->take(6)
                ->get()
        );

        return view('dashboard.index', compact(
            'company',
            'totalCash',
            'bankAccounts',
            'totalReceivables',
            'totalPayables',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'months',
            'incomeData',
            'expenseData',
            'categoryLabels',
            'categorySeries',
            'categoryColors',
            'recentInvoices',
            'recentTransactions'
        ));
    }
}
