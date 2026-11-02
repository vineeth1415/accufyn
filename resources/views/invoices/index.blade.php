@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="space-y-6">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Invoices</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Generate, track, and collect customer invoices</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md shadow-emerald-600/20 transition">
                <i class="fa-solid fa-plus text-white"></i>
                <span class="text-white force-white">Create New Invoice</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('invoices.index') }}" class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-wrap items-center gap-4 text-xs">
        <div class="flex-1 min-w-[200px]">
            <select name="customer_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-emerald-600 shadow-sm">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-emerald-600 shadow-sm">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent / Issued</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold transition">
            <i class="fa-solid fa-filter mr-1.5"></i> Filter
        </button>
        @if(request()->hasAny(['customer_id', 'status']))
            <a href="{{ route('invoices.index') }}" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 text-xs">Clear</a>
        @endif
    </form>

    <!-- Invoices Table -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-visible">
        <div class="overflow-x-auto overflow-y-visible">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4">Invoice Amount</th>
                        <th class="py-3.5 px-4">Paid Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="text-slate-900 dark:text-white font-bold">{{ $inv->customer->name ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $inv->customer->email ?? '' }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">{{ date('M d, Y', strtotime($inv->invoice_date)) }}</td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">{{ date('M d, Y', strtotime($inv->due_date)) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white text-[13px]">
                                {{ $currencySymbol }}{{ number_format($inv->total ?? $inv->total_amount ?? 0, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $currencySymbol }}{{ number_format($inv->paid_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 relative">
                                @php
                                    $badgeClasses = [
                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                        'sent' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                        'draft' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                        'overdue' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                                        'partial' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20',
                                        'cancelled' => 'bg-slate-200 text-slate-500 border-slate-300 line-through dark:bg-slate-800 dark:text-slate-400',
                                    ];
                                @endphp
                                <div class="relative inline-block text-left">
                                    <button type="button" onclick="event.stopPropagation(); toggleRowStatusMenu('{{ $inv->id }}')" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wide flex items-center gap-1.5 hover:opacity-80 transition cursor-pointer shadow-2xs {{ $badgeClasses[$inv->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}" title="Click to Change Status">
                                        <span>{{ $inv->status }}</span>
                                        <i class="fa-solid fa-chevron-down text-[8px] opacity-70"></i>
                                    </button>
                                    <div id="rowStatusMenu-{{ $inv->id }}" class="status-menu hidden absolute left-0 mt-1 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl z-50 py-1 text-xs">
                                        <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 mb-1">
                                            Change Status
                                        </div>
                                        <form action="{{ route('invoices.status', $inv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-amber-700 dark:text-amber-400 {{ $inv->status == 'pending' ? 'font-bold bg-amber-50/60' : '' }}">
                                                <i class="fa-regular fa-clock w-3.5"></i> Pending
                                            </button>
                                        </form>
                                        <form action="{{ route('invoices.status', $inv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="sent">
                                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-blue-600 dark:text-blue-400 {{ $inv->status == 'sent' ? 'font-bold bg-blue-50/60' : '' }}">
                                                <i class="fa-solid fa-paper-plane w-3.5"></i> Sent & Email
                                            </button>
                                        </form>
                                        <form action="{{ route('invoices.status', $inv->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="paid">
                                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-emerald-600 dark:text-emerald-400 {{ $inv->status == 'paid' ? 'font-bold bg-emerald-50/60' : '' }}">
                                                <i class="fa-solid fa-check w-3.5"></i> Paid
                                            </button>
                                        </form>
                                        <form action="{{ route('invoices.status', $inv->id) }}" method="POST" onsubmit="return confirm('Cancel Invoice #{{ $inv->invoice_number }}?');">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-red-600 dark:text-red-400 {{ $inv->status == 'cancelled' ? 'font-bold bg-red-50/60' : '' }}">
                                                <i class="fa-solid fa-ban w-3.5"></i> Cancelled
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                @if($inv->status === 'pending')
                                    <form action="{{ route('invoices.status', $inv->id) }}" method="POST" class="inline" title="Mark as Sent & Email Client">
                                        @csrf
                                        <input type="hidden" name="status" value="sent">
                                        <button type="submit" class="p-1.5 px-2 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-600 transition" title="Mark as Sent & Send Email">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('invoices.show', $inv->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('invoices.edit', $inv->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" title="Edit Invoice">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="Print Invoice">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('invoices.public', $inv->public_token) }}" target="_blank" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="Public Shareable Client Link">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete Invoice #{{ $inv->invoice_number }}? This will reverse customer balances and linked transactions.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Invoice">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300"></i>
                                <p>No invoices found matching criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleRowStatusMenu(id) {
        const menu = document.getElementById('rowStatusMenu-' + id);
        if (!menu) return;
        const isHidden = menu.classList.contains('hidden');
        document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
        if (isHidden) {
            menu.classList.remove('hidden');
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.status-menu')) {
            document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
        }
    });
</script>
@endsection
