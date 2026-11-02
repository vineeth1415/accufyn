@extends('layouts.app')

@section('title', 'Bills & Expenses')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Bills & Expenses</h1>
            <p class="text-xs text-slate-400">Track purchase bills, operational expenses, and vendor disbursements</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('bills.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-lg shadow-emerald-950 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Add Vendor Bill</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('bills.index') }}" class="glass-card p-4 rounded-2xl border border-slate-800 flex flex-wrap items-center gap-4 text-xs">
        <div class="flex-1 min-w-[200px]">
            <select name="vendor_id" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
                <option value="">All Vendors</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <select name="status" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500">
                <option value="">All Statuses</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl border border-slate-700 transition">
            <i class="fa-solid fa-filter mr-1.5"></i> Filter
        </button>
        @if(request()->hasAny(['vendor_id', 'status']))
            <a href="{{ route('bills.index') }}" class="text-slate-400 hover:text-slate-200">Clear</a>
        @endif
    </form>

    <!-- Bills Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Bill #</th>
                        <th class="py-3.5 px-4">Vendor</th>
                        <th class="py-3.5 px-4">Bill Date</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4">Total</th>
                        <th class="py-3.5 px-4">Paid</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($bills as $b)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-400">
                                <a href="{{ route('bills.show', $b->id) }}">{{ $b->bill_number }}</a>
                            </td>
                            <td class="py-3.5 px-4 text-white font-medium">{{ $b->vendor->name ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ date('M d, Y', strtotime($b->bill_date)) }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ date('M d, Y', strtotime($b->due_date)) }}</td>
                            <td class="py-3.5 px-4 font-bold text-white">{{ $currencySymbol }}{{ number_format($b->total_amount, 2) }}</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-400">{{ $currencySymbol }}{{ number_format($b->paid_amount, 2) }}</td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeClasses = [
                                        'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'received' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'partial' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                        'overdue' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase {{ $badgeClasses[$b->status] ?? 'bg-slate-700' }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('bills.show', $b->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('bills.edit', $b->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" title="Edit Bill">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('bills.print', $b->id) }}" target="_blank" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="Print Bill">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <form action="{{ route('bills.destroy', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete Bill #{{ $b->bill_number }}? This will reverse vendor balance and related transactions.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Bill">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">No vendor bills recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
