@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@php
    $currencySymbol = $currencySymbol ?? $company->currency_symbol ?? '₹';
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $invoice->invoice_number }}</h1>
                @php
                    $badgeClasses = [
                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                        'sent' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                        'draft' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/40 dark:text-slate-300 dark:border-slate-600',
                        'overdue' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                        'partial' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20',
                        'cancelled' => 'bg-slate-200 text-slate-500 border-slate-300 line-through dark:bg-slate-800 dark:text-slate-400',
                    ];
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border uppercase tracking-wider {{ $badgeClasses[$invoice->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Issued on {{ date('M d, Y', strtotime($invoice->invoice_date)) }} &bull; Due {{ date('M d, Y', strtotime($invoice->due_date)) }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($invoice->status === 'pending')
                <form action="{{ route('invoices.status', $invoice->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="sent">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-white"></i>
                        <span class="text-white force-white">Mark as Sent & Email Client</span>
                    </button>
                </form>
            @elseif($invoice->status === 'sent')
                <form action="{{ route('invoices.sendEmail', $invoice->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-xl border border-blue-200 dark:border-blue-800 transition flex items-center gap-1.5 shadow-sm" title="Resend Invoice Email to Customer">
                        <i class="fa-solid fa-envelope"></i> Resend Email
                    </button>
                </form>
            @endif

            <!-- Status Dropdown Menu -->
            <div class="relative inline-block text-left">
                <button type="button" onclick="event.stopPropagation(); document.getElementById('showStatusMenu').classList.toggle('hidden');" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <i class="fa-solid fa-sliders text-slate-500"></i>
                    <span>Status: <strong class="uppercase text-emerald-600 dark:text-emerald-400">{{ $invoice->status }}</strong></span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-0.5 opacity-60"></i>
                </button>
                <div id="showStatusMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl z-50 py-1.5 text-xs">
                    <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 mb-1">
                        Change Status
                    </div>
                    @if($invoice->status !== 'pending')
                    <form action="{{ route('invoices.status', $invoice->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-amber-700 dark:text-amber-400">
                            <i class="fa-regular fa-clock w-4"></i> Mark as Pending
                        </button>
                    </form>
                    @endif
                    @if($invoice->status !== 'sent')
                    <form action="{{ route('invoices.status', $invoice->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="sent">
                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-blue-600 dark:text-blue-400 font-semibold">
                            <i class="fa-solid fa-paper-plane w-4"></i> Mark as Sent & Email
                        </button>
                    </form>
                    @endif
                    @if($invoice->status !== 'paid')
                    <form action="{{ route('invoices.status', $invoice->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="paid">
                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <i class="fa-solid fa-check-double w-4"></i> Mark as Paid
                        </button>
                    </form>
                    @endif
                    @if($invoice->status !== 'cancelled')
                    <form action="{{ route('invoices.status', $invoice->id) }}" method="POST" onsubmit="return confirm('Cancel Invoice #{{ $invoice->invoice_number }}?');">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-ban w-4"></i> Mark as Cancelled
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <a href="{{ route('invoices.edit', $invoice->id) }}" class="px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print text-slate-500"></i> Print
            </a>
            <a href="{{ route('invoices.public', $invoice->public_token) }}" target="_blank" class="px-3.5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-share-nodes text-slate-500"></i> Client Link
            </a>
            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this invoice? This will reverse customer balance and transactions.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-white dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition flex items-center gap-1.5 shadow-sm" title="Delete Invoice">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </button>
            </form>
            @if(($invoice->total - $invoice->paid_amount) > 0.01 && $invoice->status !== 'cancelled')
                <button onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-transfer text-white"></i>
                    <span class="text-white force-white">Record Payment</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Printable Style Invoice Card -->
    <div class="glass-card p-8 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm space-y-8">
        <!-- Top Metadata & Company Brand -->
        <div class="flex flex-col sm:flex-row justify-between items-start border-b border-slate-100 dark:border-slate-800 pb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    @if(!empty($company->logo_path))
                        <img src="{{ asset(ltrim($company->logo_path, '/')) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="h-12 w-auto max-h-12 max-w-[180px] object-contain rounded-lg border border-slate-100 dark:border-slate-800 bg-white p-1 shadow-sm" onerror="this.style.display='none'; document.getElementById('show-default-logo-icon').style.display='flex';">
                        <div id="show-default-logo-icon" style="display: none;" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 items-center justify-center font-bold">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white leading-tight">{{ $company->name ?? 'WebotApp Enterprise' }}</h2>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Official Invoice</span>
                    </div>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $company->address ?? 'Corporate Headquarters' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $company->city ?? '' }}, {{ $company->country ?? '' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Email: {{ $company->email ?? 'billing@webotapp.com' }}</p>
                @if(!empty($company->tax_number))
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">Tax ID: {{ $company->tax_number }}</p>
                @endif
            </div>

            <div class="text-left sm:text-right">
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">INVOICE</h3>
                <p class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $invoice->invoice_number }}</p>
                @if($invoice->order_number ?? false)
                    <p class="text-xs text-slate-500 mt-0.5">PO #: {{ $invoice->order_number }}</p>
                @endif
            </div>
        </div>

        <!-- Billed To Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div>
                <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Billed To</h4>
                <h5 class="text-sm font-bold text-slate-900 dark:text-white">{{ $invoice->customer->name ?? 'Valued Customer' }}</h5>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $invoice->customer->address ?? '' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->customer->city ?? '' }}, {{ $invoice->customer->country ?? '' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Email: {{ $invoice->customer->email ?? 'N/A' }}</p>
                @if($invoice->customer->phone ?? false)
                    <p class="text-xs text-slate-500 dark:text-slate-400">Phone: {{ $invoice->customer->phone }}</p>
                @endif
            </div>

            <div class="space-y-2 text-xs sm:text-right">
                <div class="flex justify-between sm:justify-end sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400">Invoice Date:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ date('M d, Y', strtotime($invoice->invoice_date)) }}</span>
                </div>
                <div class="flex justify-between sm:justify-end sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400">Payment Due:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ date('M d, Y', strtotime($invoice->due_date)) }}</span>
                </div>
                <div class="flex justify-between sm:justify-end sm:gap-6 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-slate-700 dark:text-slate-300 font-bold">Total Due:</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold text-sm">{{ $currencySymbol }}{{ number_format($invoice->total - $invoice->paid_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-semibold border-y border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-3">Description</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Unit Price</th>
                        <th class="py-3 px-3 text-right">Tax</th>
                        <th class="py-3 px-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($invoice->items as $it)
                        <tr>
                            <td class="py-3.5 px-3 font-medium text-slate-900 dark:text-white">{{ $it->name }}</td>
                            <td class="py-3.5 px-3 text-center">{{ $it->quantity }}</td>
                            <td class="py-3.5 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->price, 2) }}</td>
                            <td class="py-3.5 px-3 text-right text-slate-500">{{ $currencySymbol }}{{ number_format($it->tax_amount, 2) }}</td>
                            <td class="py-3.5 px-3 text-right font-bold text-slate-900 dark:text-white">{{ $currencySymbol }}{{ number_format($it->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Balance -->
        <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                    <span>Subtotal:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                    <span>Taxes (GST):</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ $currencySymbol }}{{ number_format($invoice->tax_total ?? $invoice->tax_amount ?? 0, 2) }}</span>
                </div>
                @if(($invoice->discount_total ?? 0) > 0)
                    <div class="flex justify-between text-slate-500 dark:text-slate-400">
                        <span>Discount:</span>
                        <span class="text-red-500">-{{ $currencySymbol }}{{ number_format($invoice->discount_total, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                    <span>Total Amount:</span>
                    <span class="text-slate-900 dark:text-white font-extrabold">{{ $currencySymbol }}{{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Paid to Date:</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                    <span>Balance Remaining:</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-black">{{ $currencySymbol }}{{ number_format($invoice->total - $invoice->paid_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Notes & Payment Instructions</h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Record Invoice Payment</h3>
                <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('invoices.payment', $invoice->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Payment Amount ({{ $currencySymbol }}) *</label>
                    <input type="number" name="amount" value="{{ $invoice->total - $invoice->paid_amount }}" step="0.01" min="0.01" max="{{ $invoice->total - $invoice->paid_amount }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-bold focus:outline-none focus:border-emerald-600 shadow-sm">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Deposit To Bank Account *</label>
                    <select name="bank_account_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 shadow-sm">
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->bank_name ?: 'Account' }} - Bal: {{ $currencySymbol }}{{ number_format($acc->current_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 shadow-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Payment Method *</label>
                        <select name="payment_method" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 shadow-sm">
                            <option value="UPI / QR">UPI / QR</option>
                            <option value="NEFT / RTGS">NEFT / RTGS</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Reference / Transaction ID</label>
                    <input type="text" name="reference_number" placeholder="e.g. UPI-TXN-89218"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 shadow-sm">
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('showStatusMenu');
        if (menu && !e.target.closest('#showStatusMenu')) {
            menu.classList.add('hidden');
        }
    });
</script>
@endsection
