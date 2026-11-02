@extends('layouts.app')

@section('title', 'Bill ' . $bill->bill_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $bill->bill_number }}</h1>
                @php
                    $badgeClasses = [
                        'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        'received' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'partial' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        'overdue' => 'bg-red-500/10 text-red-400 border-red-500/20',
                    ];
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border uppercase {{ $badgeClasses[$bill->status] ?? 'bg-slate-700' }}">
                    {{ $bill->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Vendor: {{ $bill->vendor->name ?? 'N/A' }} &bull; Category: {{ $bill->category->name ?? 'Expense' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('bills.edit', $bill->id) }}" class="px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <a href="{{ route('bills.print', $bill->id) }}" target="_blank" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print text-slate-500"></i> Print
            </a>
            <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bill? This will reverse vendor balance and related transactions.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </button>
            </form>
            @if($bill->paid_amount < $bill->total_amount)
                <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-950 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-wave"></i> Settle / Pay Bill
                </button>
            @endif
        </div>
    </div>

    <!-- Bill Content Card -->
    <div class="glass-card p-8 rounded-2xl border border-slate-800 space-y-8 bg-slate-900/60">
        <div class="flex justify-between items-start border-b border-slate-800 pb-6">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Vendor</h3>
                <h4 class="text-base font-bold text-white">{{ $bill->vendor->name ?? 'Vendor' }}</h4>
                <p class="text-xs text-slate-400">{{ $bill->vendor->address ?? '' }}</p>
                <p class="text-xs text-slate-400">Email: {{ $bill->vendor->email ?? 'N/A' }}</p>
                <p class="text-xs text-slate-400">Phone: {{ $bill->vendor->phone ?? 'N/A' }}</p>
            </div>
            <div class="text-right space-y-1 text-xs">
                <p class="text-slate-400">Bill Date: <span class="text-white font-medium">{{ date('M d, Y', strtotime($bill->bill_date)) }}</span></p>
                <p class="text-slate-400">Due Date: <span class="text-white font-medium">{{ date('M d, Y', strtotime($bill->due_date)) }}</span></p>
                @if($bill->order_number)
                    <p class="text-slate-400">Vendor Ref #: <span class="text-white font-medium">{{ $bill->order_number }}</span></p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/70 text-slate-400 font-semibold border-y border-slate-800">
                <tr>
                    <th class="py-3 px-3">Item / Service</th>
                    <th class="py-3 px-3 text-center">Qty</th>
                    <th class="py-3 px-3 text-right">Price</th>
                    <th class="py-3 px-3 text-right">Tax</th>
                    <th class="py-3 px-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @foreach($bill->items as $it)
                    <tr>
                        <td class="py-3 px-3 font-medium text-white">{{ $it->name }}</td>
                        <td class="py-3 px-3 text-center">{{ $it->quantity }}</td>
                        <td class="py-3 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->price, 2) }}</td>
                        <td class="py-3 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->tax_amount, 2) }}</td>
                        <td class="py-3 px-3 text-right font-semibold text-white">{{ $currencySymbol }}{{ number_format($it->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Balances -->
        <div class="flex justify-end pt-4 border-t border-slate-800">
            <div class="w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span class="text-white font-medium">{{ $currencySymbol }}{{ number_format($bill->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Taxes:</span>
                    <span class="text-white font-medium">{{ $currencySymbol }}{{ number_format($bill->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-white pt-2 border-t border-slate-800">
                    <span>Bill Amount:</span>
                    <span class="text-white font-extrabold">{{ $currencySymbol }}{{ number_format($bill->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Paid to Date:</span>
                    <span class="text-emerald-400 font-semibold">{{ $currencySymbol }}{{ number_format($bill->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-white pt-2 border-t border-slate-800">
                    <span>Balance Due:</span>
                    <span class="text-red-400 font-extrabold">{{ $currencySymbol }}{{ number_format($bill->total_amount - $bill->paid_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Record Bill Payment</h3>
                <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('bills.payment', $bill->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Payment Amount ($) *</label>
                    <input type="number" name="amount" value="{{ $bill->total_amount - $bill->paid_amount }}" step="0.01" min="0.01" max="{{ $bill->total_amount - $bill->paid_amount }}" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-bold focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Deduct From Bank Account *</label>
                    <select name="bank_account_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (Bal: {{ $company->currency_symbol ?? ($currencySymbol ?? '₹') }}{{ number_format($acc->current_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Payment Method *</label>
                        <select name="payment_method" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Credit Card">Credit Card</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="e.g. CHQ-9218 or Bank Ref"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
