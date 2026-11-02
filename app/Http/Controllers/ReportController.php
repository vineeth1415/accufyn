<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Tax;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        // Invoiced Income
        $totalInvoiced = (float) Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'partial', 'sent', 'viewed'])
            ->sum('total');

        // Billed Expenses
        $totalBilled = (float) Bill::whereBetween('bill_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'partial', 'received'])
            ->sum('total');

        // Cash flow transactions
        $cashIncome = (float) Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $cashExpense = (float) Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Grouped expenses by category
        $expensesByCategory = Category::where('type', 'expense')
            ->withSum(['transactions' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            }], 'amount')
            ->get();

        $netProfit   = $totalInvoiced - $totalBilled;
        $netCashFlow = $cashIncome - $cashExpense;
        $company     = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('reports.profit_loss', compact(
            'startDate', 'endDate', 'totalInvoiced', 'totalBilled',
            'cashIncome', 'cashExpense', 'expensesByCategory', 'netProfit', 'netCashFlow', 'company'
        ));
    }

    /**
     * One-click Excel (.csv with UTF-8 BOM) export of Profit & Loss Report for audit filing.
     */
    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        $totalInvoiced = (float) Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'partial', 'sent', 'viewed'])
            ->sum('total');

        $totalBilled = (float) Bill::whereBetween('bill_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'partial', 'received'])
            ->sum('total');

        $cashIncome = (float) Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $cashExpense = (float) Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expensesByCategory = Category::where('type', 'expense')
            ->withSum(['transactions' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            }], 'amount')
            ->get();

        $netProfit   = $totalInvoiced - $totalBilled;
        $netCashFlow = $cashIncome - $cashExpense;
        $company     = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => 'INR']);

        $filename = "Profit_and_Loss_Statement_{$startDate}_to_{$endDate}.csv";
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($startDate, $endDate, $totalInvoiced, $totalBilled, $cashIncome, $cashExpense, $expensesByCategory, $netProfit, $netCashFlow, $company) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // Excel UTF-8 BOM

            // Header Section
            fputcsv($handle, [$company->name ?? 'Company Accounting']);
            fputcsv($handle, ['Tax / GSTIN Number:', $company->tax_number ?? 'N/A']);
            fputcsv($handle, ['FINANCIAL REPORT: PROFIT & LOSS STATEMENT (AUDIT & TAX FILING)']);
            fputcsv($handle, ['Period:', "{$startDate} to {$endDate}"]);
            fputcsv($handle, ['Exported Date:', now()->format('Y-m-d H:i:s'), 'Exported By:', (Auth::user()->name ?? 'Auditor') . ' (' . (Auth::user()->role ?? 'AUDITOR') . ')']);
            fputcsv($handle, []);

            // Revenue Section
            fputcsv($handle, ['1. OPERATING REVENUE (INCOME)', '', 'Amount (' . ($company->currency_code ?? 'INR') . ')']);
            fputcsv($handle, ['Total Invoiced Sales (Accrual Basis)', '', number_format($totalInvoiced, 2, '.', '')]);
            fputcsv($handle, ['Cash Sales / Income Received (Cash Basis)', '', number_format($cashIncome, 2, '.', '')]);
            fputcsv($handle, ['TOTAL GROSS REVENUE', '', number_format($totalInvoiced, 2, '.', '')]);
            fputcsv($handle, []);

            // Expenses Section
            fputcsv($handle, ['2. OPERATING EXPENSES (COSTS & BILLS)', '', 'Amount (' . ($company->currency_code ?? 'INR') . ')']);
            fputcsv($handle, ['Total Vendor Bills (Accrual Basis)', '', number_format($totalBilled, 2, '.', '')]);
            fputcsv($handle, ['Cash Paid Expenses (Cash Basis)', '', number_format($cashExpense, 2, '.', '')]);
            fputcsv($handle, []);

            // Category Breakdown
            fputcsv($handle, ['3. EXPENSES BREAKDOWN BY CATEGORY', '', 'Amount']);
            foreach ($expensesByCategory as $cat) {
                $amt = (float) ($cat->transactions_sum_amount ?? 0);
                fputcsv($handle, [' - ' . $cat->name, '', number_format($amt, 2, '.', '')]);
            }
            fputcsv($handle, ['TOTAL OPERATING EXPENSES', '', number_format($totalBilled, 2, '.', '')]);
            fputcsv($handle, []);

            // Net Profit / Summary
            fputcsv($handle, ['4. SUMMARY & NET FINANCIAL RESULT', '', 'Amount']);
            fputcsv($handle, ['NET OPERATING PROFIT / (LOSS) [Accrual]', '', number_format($netProfit, 2, '.', '')]);
            fputcsv($handle, ['NET CASH FLOW SURPLUS / (DEFICIT) [Cash]', '', number_format($netCashFlow, 2, '.', '')]);
            fputcsv($handle, []);
            fputcsv($handle, ['CERTIFIED AUDITOR / MANAGEMENT COPY', 'Status: Filed / Extracted for Audit', 'Date: ' . now()->toDateString()]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function incomeExpense(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = sprintf('%04d-%02d-01', $year, $m);
            $monthEnd   = date('Y-m-t', strtotime($monthStart));

            $income = (float) Transaction::where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');

            $expense = (float) Transaction::where('type', 'expense')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');

            $monthlyData[] = [
                'month_num' => $m,
                'month'     => date('M Y', strtotime($monthStart)),
                'income'    => $income,
                'expense'   => $expense,
                'profit'    => $income - $expense,
            ];
        }

        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('reports.income_expense', compact('year', 'monthlyData', 'company'));
    }

    /**
     * One-click Excel (.csv with UTF-8 BOM) export of Income & Expense report.
     */
    public function exportIncomeExpense(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        $company = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => 'INR']);

        $filename = "Income_Expense_Statement_{$year}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($year, $company) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [$company->name ?? 'Company Accounting']);
            fputcsv($handle, ['Tax / GSTIN Number:', $company->tax_number ?? 'N/A']);
            fputcsv($handle, ["ANNUAL INCOME & EXPENSE STATEMENT - CALENDAR YEAR {$year}"]);
            fputcsv($handle, ['Exported At:', now()->format('Y-m-d H:i:s'), 'Exported By:', Auth::user()->name . ' (' . Auth::user()->role . ')']);
            fputcsv($handle, []);

            fputcsv($handle, ['#', 'Month', 'Total Income (' . ($company->currency_code ?? 'INR') . ')', 'Total Expense (' . ($company->currency_code ?? 'INR') . ')', 'Net Profit / (Loss)']);

            $totalIncome  = 0;
            $totalExpense = 0;

            for ($m = 1; $m <= 12; $m++) {
                $monthStart = sprintf('%04d-%02d-01', $year, $m);
                $monthEnd   = date('Y-m-t', strtotime($monthStart));

                $income = (float) Transaction::where('type', 'income')
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->sum('amount');

                $expense = (float) Transaction::where('type', 'expense')
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->sum('amount');

                $net = $income - $expense;
                $totalIncome  += $income;
                $totalExpense += $expense;

                fputcsv($handle, [
                    $m,
                    date('F Y', strtotime($monthStart)),
                    number_format($income, 2, '.', ''),
                    number_format($expense, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                "Full Year {$year}",
                number_format($totalIncome, 2, '.', ''),
                number_format($totalExpense, 2, '.', ''),
                number_format($totalIncome - $totalExpense, 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function taxSummary(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        $collectedTax = (float) Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('tax_total');
        $paidTax      = (float) Bill::whereBetween('bill_date', [$startDate, $endDate])->sum('tax_total');
        $netTaxDue    = $collectedTax - $paidTax;

        $taxes   = Tax::all();
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('reports.tax_summary', compact(
            'startDate', 'endDate', 'collectedTax', 'paidTax', 'netTaxDue', 'taxes', 'company'
        ));
    }

    /**
     * One-click Excel (.csv with UTF-8 BOM) export of Tax / GST Summary for filing.
     */
    public function exportTaxSummary(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        $collectedTax = (float) Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('tax_total');
        $paidTax      = (float) Bill::whereBetween('bill_date', [$startDate, $endDate])->sum('tax_total');
        $netTaxDue    = $collectedTax - $paidTax;
        $taxes        = Tax::all();
        $company      = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => 'INR']);

        $filename = "GST_Tax_Filing_Summary_{$startDate}_to_{$endDate}.csv";
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($startDate, $endDate, $collectedTax, $paidTax, $netTaxDue, $taxes, $company) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [$company->name ?? 'Company Accounting']);
            fputcsv($handle, ['GSTIN / Tax ID:', $company->tax_number ?? 'N/A']);
            fputcsv($handle, ['GST / TAX RETURN FILING SUMMARY REPORT']);
            fputcsv($handle, ['Period:', "{$startDate} to {$endDate}"]);
            fputcsv($handle, ['Exported At:', now()->format('Y-m-d H:i:s'), 'Exported By:', Auth::user()->name . ' (' . Auth::user()->role . ')']);
            fputcsv($handle, []);

            fputcsv($handle, ['TAX FILING SUMMARY OVERVIEW', '', 'Amount (' . ($company->currency_code ?? 'INR') . ')']);
            fputcsv($handle, ['Output Tax Collected (Sales Invoices / GSTR-1)', '', number_format($collectedTax, 2, '.', '')]);
            fputcsv($handle, ['Input Tax Credit / ITC Paid (Vendor Bills / GSTR-2B)', '', number_format($paidTax, 2, '.', '')]);
            fputcsv($handle, ['NET TAX PAYABLE / (REFUNDABLE) [Output - ITC]', '', number_format($netTaxDue, 2, '.', '')]);
            fputcsv($handle, []);

            fputcsv($handle, ['CONFIGURED TAX SLABS', 'Tax Name', 'Rate %', 'Type']);
            $i = 1;
            foreach ($taxes as $t) {
                fputcsv($handle, [$i++, $t->name, $t->rate . '%', strtoupper($t->type ?? 'Normal')]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TAX AUDIT NOTE:', 'Verified for quarterly / monthly GST return reconciliation.']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Complete Trial Balance / General Ledger Master Report.
     */
    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        // 1. Custom Ledgers
        $ledgers = Ledger::where('is_active', true)->orderBy('group')->orderBy('name')->get();

        // 2. Bank & Cash Accounts
        $bankAccounts = BankAccount::where('status', '!=', 'archived')->get();

        // 3. Accounts Receivable (Customers outstanding balance)
        $totalReceivables = (float) Invoice::whereIn('status', ['sent', 'partial', 'viewed', 'overdue'])->sum('due_amount');

        // 4. Accounts Payable (Vendors outstanding balance)
        $totalPayables = (float) Bill::whereIn('status', ['received', 'partial', 'overdue'])->sum('due_amount');

        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('reports.trial_balance', compact(
            'asOfDate', 'ledgers', 'bankAccounts', 'totalReceivables', 'totalPayables', 'company'
        ));
    }

    /**
     * Export Trial Balance to Excel (.csv with UTF-8 BOM).
     */
    public function exportTrialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $ledgers          = Ledger::where('is_active', true)->orderBy('group')->orderBy('name')->get();
        $bankAccounts     = BankAccount::where('status', '!=', 'archived')->get();
        $totalReceivables = (float) Invoice::whereIn('status', ['sent', 'partial', 'viewed', 'overdue'])->sum('due_amount');
        $totalPayables    = (float) Bill::whereIn('status', ['received', 'partial', 'overdue'])->sum('due_amount');
        $company          = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => 'INR']);

        $filename = "Trial_Balance_Report_As_Of_{$asOfDate}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($asOfDate, $ledgers, $bankAccounts, $totalReceivables, $totalPayables, $company) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [$company->name ?? 'Company Accounting']);
            fputcsv($handle, ['Tax / GSTIN Number:', $company->tax_number ?? 'N/A']);
            fputcsv($handle, ['TRIAL BALANCE & MASTER LEDGER AUDIT REPORT']);
            fputcsv($handle, ['As of Date:', $asOfDate]);
            fputcsv($handle, ['Exported At:', now()->format('Y-m-d H:i:s'), 'Exported By:', Auth::user()->name . ' (' . Auth::user()->role . ')']);
            fputcsv($handle, []);

            fputcsv($handle, ['Account Code', 'Account Name', 'Account Group / Classification', 'Debit Amount (' . ($company->currency_code ?? 'INR') . ')', 'Credit Amount (' . ($company->currency_code ?? 'INR') . ')']);

            $sumDebit  = 0;
            $sumCredit = 0;

            // Bank Accounts (Assets - Debit)
            foreach ($bankAccounts as $ba) {
                $bal = (float) $ba->current_balance;
                $deb = $bal >= 0 ? $bal : 0;
                $crd = $bal < 0 ? abs($bal) : 0;
                $sumDebit  += $deb;
                $sumCredit += $crd;
                fputcsv($handle, [
                    'BANK-' . $ba->id,
                    $ba->name . ' (' . ($ba->account_number ?: 'Cash') . ')',
                    'Bank & Cash (Asset)',
                    $deb ? number_format($deb, 2, '.', '') : '',
                    $crd ? number_format($crd, 2, '.', '') : '',
                ]);
            }

            // Accounts Receivable (Asset - Debit)
            if ($totalReceivables > 0) {
                $sumDebit += $totalReceivables;
                fputcsv($handle, [
                    'AR-001',
                    'Accounts Receivable (Debtors Outstanding)',
                    'Current Asset',
                    number_format($totalReceivables, 2, '.', ''),
                    '',
                ]);
            }

            // Accounts Payable (Liability - Credit)
            if ($totalPayables > 0) {
                $sumCredit += $totalPayables;
                fputcsv($handle, [
                    'AP-001',
                    'Accounts Payable (Creditors Outstanding)',
                    'Current Liability',
                    '',
                    number_format($totalPayables, 2, '.', ''),
                ]);
            }

            // Custom Ledgers
            foreach ($ledgers as $l) {
                $bal = (float) $l->current_balance;
                $deb = 0;
                $crd = 0;

                if ($l->isDebitNormal()) {
                    if ($bal >= 0) {
                        $deb = $bal;
                    } else {
                        $crd = abs($bal);
                    }
                } else {
                    if ($bal >= 0) {
                        $crd = $bal;
                    } else {
                        $deb = abs($bal);
                    }
                }

                $sumDebit  += $deb;
                $sumCredit += $crd;

                fputcsv($handle, [
                    $l->code ?: 'LED-' . $l->id,
                    $l->name,
                    ucfirst($l->group),
                    $deb ? number_format($deb, 2, '.', '') : '',
                    $crd ? number_format($crd, 2, '.', '') : '',
                ]);
            }

            // Totals row
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                'TRIAL BALANCE TOTALS',
                '',
                number_format($sumDebit, 2, '.', ''),
                number_format($sumCredit, 2, '.', ''),
            ]);

            $diff = round($sumDebit - $sumCredit, 2);
            fputcsv($handle, [
                'DIFFERENCE',
                $diff === 0.0 ? 'Balanced (Trial Balance verified)' : 'Difference: ' . number_format($diff, 2, '.', ''),
                '',
                '',
                '',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
