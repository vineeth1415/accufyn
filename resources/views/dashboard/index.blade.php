@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Cash in Banks -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Cash & Banks</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm">
                    <i class="fa-solid fa-vault"></i>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold text-white">{{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($totalCash, 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                    <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-circle-check"></i> Reconciled</span> across {{ count($bankAccounts) }} accounts
                </p>
            </div>
        </div>

        <!-- Receivables -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-xl group-hover:bg-blue-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Accounts Receivable</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400 text-sm">
                    <i class="fa-solid fa-arrow-down-left"></i>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold text-white">{{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($totalReceivables, 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Pending customer collections</p>
            </div>
        </div>

        <!-- Payables -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Accounts Payable</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 text-sm">
                    <i class="fa-solid fa-arrow-up-right"></i>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold text-white">{{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($totalPayables, 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Unpaid vendor obligations</p>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500/5 rounded-full blur-xl group-hover:bg-purple-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Net Profit</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 text-sm">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($netProfit, 2) }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-1">Total revenue minus expenses</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cash Flow 6 Months -->
        <div class="lg:col-span-2 glass-card p-5 rounded-2xl border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-white">Cash Flow Dynamics</h3>
                    <p class="text-xs text-slate-400">Monthly income vs expenses over last 6 months</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1 text-slate-300">
                        <span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Income
                    </span>
                    <span class="flex items-center gap-1 text-slate-300">
                        <span class="w-3 h-3 rounded bg-red-500 inline-block"></span> Expense
                    </span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>

        <!-- Expense by Category -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-white">Expense Distribution</h3>
                <p class="text-xs text-slate-400">Cost allocation breakdown by category</p>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="expenseCategoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Dual Table: Recent Invoices & Recent Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Recent Invoices</h3>
                    <p class="text-xs text-slate-400">Latest sales billing activity</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/80 text-slate-400 font-semibold border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($recentInvoices as $inv)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-bold text-emerald-400">
                                    <a href="{{ route('invoices.show', $inv->id) }}">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="py-3 px-4 text-white font-medium">{{ $inv->customer->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 font-semibold text-white">{{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($inv->total, 2) }}</td>
                                <td class="py-3 px-4">
                                    @php
                                        $badgeClasses = [
                                            'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'sent' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'draft' => 'bg-slate-700/40 text-slate-300 border-slate-600',
                                            'overdue' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'partial' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase {{ $badgeClasses[$inv->status] ?? 'bg-slate-700' }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">No invoices generated yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Ledger Transactions -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Live Banking Ledger</h3>
                    <p class="text-xs text-slate-400">Real-time debits and credits</p>
                </div>
                <a href="{{ route('banking.transactions') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">View Ledger &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/80 text-slate-400 font-semibold border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Account / Ref</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 text-slate-400">{{ date('M d, Y', strtotime($tx->transaction_date)) }}</td>
                                <td class="py-3 px-4">
                                    <p class="text-white font-medium truncate max-w-[140px]">{{ $tx->bankAccount->name ?? 'Default' }}</p>
                                    <p class="text-[10px] text-slate-500 truncate max-w-[140px]">{{ $tx->description }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase {{ $tx->type === 'income' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold {{ $tx->type === 'income' ? 'text-emerald-400' : 'text-slate-200' }}">
                                    {{ $tx->type === 'income' ? '+' : '-' }}{{ $currencySymbol ?? ($company->currency_symbol ?? '₹') }}{{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
        const months = {!! json_encode($months) !!};
        const incomeData = {!! json_encode($incomeData) !!};
        const expenseData = {!! json_encode($expenseData) !!};

        new Chart(cashFlowCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense',
                        data: expenseData,
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b' }
                    },
                    y: {
                        grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b' }
                    }
                }
            }
        });

        // Expense Category Chart
        const expenseCtx = document.getElementById('expenseCategoryChart').getContext('2d');
        const catLabels = {!! json_encode($categoryLabels) !!};
        const catAmounts = {!! json_encode($categorySeries) !!};
        const catColors = {!! json_encode($categoryColors) !!};

        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels.length ? catLabels : ['Operating'],
                datasets: [{
                    data: catAmounts.length ? catAmounts : [100],
                    backgroundColor: catColors.length ? catColors : ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#475569', font: { size: 10 } }
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endsection
