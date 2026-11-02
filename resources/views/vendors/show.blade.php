@extends('layouts.app')

@section('title', $vendor->name . ' - Vendor Profile')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $vendor->name }}</h1>
            <p class="text-xs text-slate-500">Vendor expense history, contact information, and payable obligations</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('bills.create') }}?vendor_id={{ $vendor->id }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
                <i class="fa-solid fa-plus text-white"></i>
                <span class="text-white force-white">Create Purchase Bill</span>
            </a>
            <a href="{{ route('vendors.index') }}" class="text-xs text-slate-500 hover:text-slate-800">&larr; Back to Vendors</a>
        </div>
    </div>

    <!-- Vendor Profile Details Card -->
    <div class="glass-card p-5 rounded-2xl border border-slate-200 shadow-sm bg-white">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
            <i class="fa-solid fa-address-card text-emerald-600"></i>
            <span>Supplier Information & Contact Details</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Business / Company Name</span>
                <p class="text-slate-900 font-bold mt-0.5">{{ $vendor->company_name ?: $vendor->name }}</p>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Email Address</span>
                <p class="text-slate-900 font-semibold mt-0.5">
                    @if($vendor->email)
                        <a href="mailto:{{ $vendor->email }}" class="text-emerald-600 hover:underline">{{ $vendor->email }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Phone Number</span>
                <p class="text-slate-900 font-semibold mt-0.5">{{ $vendor->phone ?: '—' }}</p>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">GSTIN / Tax ID</span>
                <p class="text-slate-900 font-mono font-bold mt-0.5 uppercase">{{ $vendor->tax_number ?: 'Not Registered' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs mt-4 pt-3 border-t border-slate-100">
            <div class="md:col-span-2">
                <span class="text-slate-400 block font-medium">Billing Address</span>
                <p class="text-slate-800 mt-0.5">{{ $vendor->address ?: 'No address registered' }}</p>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">City & Location</span>
                <p class="text-slate-800 mt-0.5">{{ $vendor->city ? ($vendor->city . ', ' . ($vendor->country ?? 'India')) : ($vendor->country ?? '—') }}</p>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Status</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active Supplier
                </span>
            </div>
        </div>
    </div>

    <!-- Overview Financial Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="glass-card p-5 rounded-2xl border border-slate-200 shadow-sm bg-white">
            <span class="text-xs text-slate-500 uppercase font-semibold">Total Invoiced / Billed</span>
            <h3 class="text-xl font-bold text-slate-900 mt-1">{{ $currencySymbol }}{{ number_format($vendor->bills->sum('total'), 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Across {{ count($vendor->bills) }} recorded bills</p>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-200 shadow-sm bg-white">
            <span class="text-xs text-slate-500 uppercase font-semibold">Total Settled / Paid</span>
            <h3 class="text-xl font-bold text-emerald-600 mt-1">{{ $currencySymbol }}{{ number_format($vendor->bills->sum('paid_amount'), 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Payments made to supplier</p>
        </div>
        <div class="glass-card p-5 rounded-2xl border border-slate-200 shadow-sm bg-white">
            <span class="text-xs text-slate-500 uppercase font-semibold">Payable Outstanding Due</span>
            <h3 class="text-xl font-bold {{ $vendor->outstanding_balance > 0 ? 'text-red-600' : 'text-slate-800' }} mt-1">
                {{ $currencySymbol }}{{ number_format($vendor->outstanding_balance, 2) }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">Current pending obligations</p>
        </div>
    </div>

    <!-- Bills List -->
    <div class="glass-card rounded-2xl border border-slate-200 shadow-sm bg-white overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Purchase Bills History</h3>
                <p class="text-[11px] text-slate-500">Itemized bills and expenses received from {{ $vendor->name }}</p>
            </div>
            <a href="{{ route('bills.create') }}?vendor_id={{ $vendor->id }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">+ Add Bill</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Bill #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                        <th class="py-3 px-4 text-right">Paid</th>
                        <th class="py-3 px-4 text-right">Balance Due</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($vendor->bills as $b)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-600">
                                <a href="{{ route('bills.show', $b->id) }}">{{ $b->bill_number }}</a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">{{ date('M d, Y', strtotime($b->bill_date)) }}</td>
                            <td class="py-3.5 px-4 text-slate-500">{{ date('M d, Y', strtotime($b->due_date)) }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($b->total, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-semibold text-emerald-600">{{ $currencySymbol }}{{ number_format($b->paid_amount, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-red-600">{{ $currencySymbol }}{{ number_format($b->due_amount, 2) }}</td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeClasses = [
                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'received' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'overdue' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase {{ $badgeClasses[$b->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('bills.show', $b->id) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">View &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No purchase bills recorded for this supplier yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
