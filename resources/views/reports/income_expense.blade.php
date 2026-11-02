@extends('layouts.app')

@section('title', 'Monthly Cash Flow Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Monthly Cash Flow Breakdown</h1>
            <p class="text-xs text-slate-400">12-month income vs expense performance</p>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <a href="{{ route('reports.profit_loss') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 border border-slate-200 dark:border-slate-700 shadow-sm transition">Profit &amp; Loss</a>
            <a href="{{ route('reports.income_expense') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">Monthly Cashflow</a>
            <a href="{{ route('reports.tax_summary') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 border border-slate-200 dark:border-slate-700 shadow-sm transition">Tax Summary</a>
            <a href="{{ route('reports.trial_balance') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 border border-slate-200 dark:border-slate-700 shadow-sm transition">Trial Balance</a>
        </div>
    </div>

    <!-- Filter Bar & Excel Export -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" action="{{ route('reports.income_expense') }}" class="flex items-center gap-3">
            <span class="text-slate-500 font-semibold">Select Year:</span>
            <select name="year" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-slate-800 dark:text-white">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-sm transition">Update</button>
        </form>

        <a href="{{ route('reports.income_expense.export', ['year' => $year]) }}" 
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl shadow-sm transition">
            <i class="fa-solid fa-file-excel text-white"></i>
            <span class="text-white force-white">Export to Excel</span>
        </a>
    </div>

    <!-- Annual Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Month</th>
                        <th class="py-3.5 px-4 text-right">Income Cash In</th>
                        <th class="py-3.5 px-4 text-right">Expense Cash Out</th>
                        <th class="py-3.5 px-4 text-right">Net Flow</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @foreach($monthlyData as $row)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-white">{{ $row['month'] }} {{ $year }}</td>
                            <td class="py-3.5 px-4 text-right font-semibold text-emerald-400">{{ $currencySymbol }}{{ number_format($row['income'], 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-semibold text-red-400">{{ $currencySymbol }}{{ number_format($row['expense'], 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $row['profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $currencySymbol }}{{ number_format($row['profit'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
