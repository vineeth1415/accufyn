@extends('layouts.app')

@section('title', 'Ledger Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Financial Ledger</h1>
            <p class="text-xs text-slate-400">Complete historical journal of all debits, credits, and balance updates</p>
        </div>
        <a href="{{ route('banking.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Back to Banking</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('banking.transactions') }}" class="glass-card p-4 rounded-2xl border border-slate-800 flex flex-wrap items-center gap-4 text-xs">
        <div class="min-w-[180px]">
            <select name="bank_account_id" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
                <option value="">All Bank Accounts</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ request('bank_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->account_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <select name="type" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
                <option value="">All Types</option>
                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income (+)</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense (-)</option>
            </select>
        </div>
        <div>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
        </div>
        <div>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl border border-slate-700 transition">
            <i class="fa-solid fa-filter mr-1.5"></i> Filter
        </button>
        @if(request()->hasAny(['bank_account_id', 'type', 'start_date', 'end_date']))
            <a href="{{ route('banking.transactions') }}" class="text-slate-400 hover:text-slate-200">Reset</a>
        @endif
    </form>

    <!-- Ledger Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Account</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Method / Ref</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4 text-right">Amount</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-slate-400">{{ date('M d, Y', strtotime($tx->transaction_date)) }}</td>
                            <td class="py-3.5 px-4 font-medium text-white">{{ $tx->bankAccount->account_name ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $tx->category->name ?? 'General' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase {{ $tx->type === 'income' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                    {{ $tx->type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                <p>{{ $tx->payment_method ?? 'Bank' }}</p>
                                @if($tx->reference_number)
                                    <p class="text-[10px] text-slate-500">{{ $tx->reference_number }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-300 max-w-xs truncate">{{ $tx->description }}</td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $tx->type === 'income' ? 'text-emerald-400' : 'text-slate-200' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}{{ $currencySymbol ?? '₹' }}{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('banking.transactions.destroy', $tx->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction? The bank account balance will be automatically adjusted.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Transaction">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">No transactions recorded in ledger.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
