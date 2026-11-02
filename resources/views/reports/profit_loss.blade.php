@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Financial Reports</h1>
            <p class="text-xs text-slate-500">Comprehensive accounting statements, audit ledgers, and tax liabilities</p>
        </div>
        <!-- Report Subnav -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <a href="{{ route('reports.profit_loss') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">Profit & Loss</a>
            <a href="{{ route('reports.income_expense') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm transition">Monthly Cashflow</a>
            <a href="{{ route('reports.tax_summary') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm transition">Tax Summary</a>
            <a href="{{ route('reports.trial_balance') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm transition">Trial Balance</a>
        </div>
    </div>

    <!-- Date Range Filter & One-Click Excel Export -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 shadow-sm bg-white flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" action="{{ route('reports.profit_loss') }}" class="flex flex-wrap items-center gap-3">
            <span class="text-slate-500 font-semibold">Reporting Period:</span>
            <input type="date" name="start_date" value="{{ $startDate }}" class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-600 shadow-sm">
            <span class="text-slate-400 font-medium">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-600 shadow-sm">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-semibold shadow-sm transition">Update Statement</button>
        </form>

        <a href="{{ route('reports.profit_loss.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl shadow-sm transition">
            <i class="fa-solid fa-file-excel text-white"></i>
            <span class="text-white force-white">Export to Excel</span>
        </a>
    </div>

    <!-- P&L Sheet -->
    <div class="glass-card p-8 rounded-2xl border border-slate-200 shadow-sm bg-white max-w-3xl mx-auto space-y-6">
        <div class="text-center border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Profit & Loss Statement</h2>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">For period: {{ date('M d, Y', strtotime($startDate)) }} &ndash; {{ date('M d, Y', strtotime($endDate)) }}</p>
            <p class="text-[11px] text-slate-400 mt-1 uppercase tracking-wider font-semibold">{{ $company->name ?? 'WebotApp Enterprise' }} &bull; Standard Financial Year: {{ $company->financial_year ?? 'April - March' }}</p>
        </div>

        <!-- Operating Revenue -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-emerald-700 uppercase tracking-wider">1. Operating Income / Revenue</h3>
                <span class="text-[11px] text-slate-400 font-medium">Sales & Cash Inflow</span>
            </div>
            
            <div class="p-4 rounded-xl space-y-2.5 text-xs border border-slate-100 divide-y divide-slate-50 bg-white">
                <div class="flex justify-between text-slate-700 pt-1 first:pt-0">
                    <span class="font-medium">Gross Sales & Invoiced Billings</span>
                    <span class="font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($totalInvoiced, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-500 pt-2 text-[11px]">
                    <span>Total Cash Collections Received</span>
                    <span class="font-medium text-slate-700">{{ $currencySymbol }}{{ number_format($cashIncome, 2) }}</span>
                </div>
            </div>

            <div class="flex justify-between text-xs font-bold text-slate-900 px-2 pt-1">
                <span>Total Revenue (Accrual)</span>
                <span class="text-emerald-700 font-extrabold text-sm">{{ $currencySymbol }}{{ number_format($totalInvoiced, 2) }}</span>
            </div>
        </div>

        <!-- Operating Expenses -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-red-700 uppercase tracking-wider">2. Operating Expenses & Bills</h3>
                <span class="text-[11px] text-slate-400 font-medium">Categorized Outflow</span>
            </div>

            <div class="p-4 rounded-xl space-y-2 text-xs divide-y divide-slate-100 border border-slate-100 bg-white">
                @forelse($expensesByCategory as $ec)
                    <div class="flex justify-between text-slate-700 pt-2 first:pt-0">
                        <span class="font-medium">{{ $ec->name }}</span>
                        <span class="font-semibold text-slate-900">{{ $currencySymbol }}{{ number_format($ec->transactions_sum_amount ?? 0, 2) }}</span>
                    </div>
                @empty
                    <div class="text-slate-400 text-center py-2">No category expenses recorded in this period.</div>
                @endforelse
            </div>

            <div class="flex justify-between text-xs font-bold text-slate-900 px-2 pt-1">
                <span>Total Vendor Bills & Expenses</span>
                <span class="text-red-700 font-extrabold text-sm">{{ $currencySymbol }}{{ number_format($totalBilled, 2) }}</span>
            </div>
        </div>

        <!-- Net Profit Summary -->
        <div class="pt-5 border-t-2 border-slate-200 space-y-3">
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 flex justify-between items-center text-sm font-bold shadow-sm">
                <span class="text-slate-900">Net Income (Accrual Profit)</span>
                <span class="{{ $netProfit >= 0 ? 'text-emerald-700 font-black text-lg' : 'text-red-700 font-black text-lg' }}">
                    {{ $currencySymbol }}{{ number_format($netProfit, 2) }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white flex justify-between items-center text-xs">
                <span class="text-slate-500 font-medium">Net Cash Inflow (Cash Basis)</span>
                <span class="font-bold {{ $netCashFlow >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    {{ $currencySymbol }}{{ number_format($netCashFlow, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
