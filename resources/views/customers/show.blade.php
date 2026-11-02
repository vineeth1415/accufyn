@extends('layouts.app')

@section('title', $customer->name . ' - Customer Profile')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">{{ $customer->name }}</h1>
            <p class="text-xs text-slate-400">Customer account ledger and billing history</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Back to Customers</a>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="glass-card p-5 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-semibold">Total Invoiced</span>
            <h3 class="text-xl font-bold text-white mt-1">{{ $currencySymbol }}{{ number_format($customer->invoices->sum('total_amount'), 2) }}</h3>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-semibold">Total Paid</span>
            <h3 class="text-xl font-bold text-emerald-400 mt-1">{{ $currencySymbol }}{{ number_format($customer->invoices->sum('paid_amount'), 2) }}</h3>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-semibold">Outstanding Due</span>
            <h3 class="text-xl font-bold text-amber-400 mt-1">{{ $currencySymbol }}{{ number_format($customer->outstanding_balance, 2) }}</h3>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Invoices History</h3>
            <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">+ Create Invoice</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/80 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($customer->invoices as $inv)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-400">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4 text-slate-400">{{ date('M d, Y', strtotime($inv->invoice_date)) }}</td>
                            <td class="py-3 px-4 text-slate-400">{{ date('M d, Y', strtotime($inv->due_date)) }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $currencySymbol }}{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase bg-slate-800">{{ $inv->status }}</span></td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="text-emerald-400 hover:text-emerald-300">View &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">No invoices issued for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
