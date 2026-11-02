<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - WebotApp Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full bg-slate-950 py-10 px-4 sm:px-6">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Brand Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(!empty($company->logo_path))
                    <img src="{{ asset(ltrim($company->logo_path, '/')) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="h-11 w-auto max-h-11 max-w-[160px] object-contain rounded-lg border border-slate-800 bg-slate-900/60 p-1" onerror="this.style.display='none'; document.getElementById('public-default-logo-icon').style.display='flex';">
                    <div id="public-default-logo-icon" style="display: none;" class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 items-center justify-center text-emerald-400 font-bold">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 font-bold">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                @endif
                <div>
                    <h2 class="text-sm font-bold text-white leading-tight">{{ $company->name ?? 'WebotApp Enterprise' }}</h2>
                    <p class="text-[10px] text-slate-400">Client Invoicing Portal</p>
                </div>
            </div>
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print / Save PDF
            </button>
        </div>

        <!-- Invoice Card -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-8 backdrop-blur shadow-2xl space-y-8">
            <div class="flex justify-between items-start border-b border-slate-800 pb-6">
                <div>
                    <h3 class="text-xl font-bold text-white uppercase tracking-tight">INVOICE</h3>
                    <p class="text-xs font-mono font-bold text-emerald-400 mt-0.5">{{ $invoice->invoice_number }}</p>
                </div>
                <div class="text-right">
                    @php
                        $badgeClasses = [
                            'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            'sent' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                            'draft' => 'bg-slate-700/40 text-slate-300 border-slate-600',
                            'overdue' => 'bg-red-500/10 text-red-400 border-red-500/20',
                            'partial' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border uppercase {{ $badgeClasses[$invoice->status] ?? 'bg-slate-700' }}">
                        {{ $invoice->status }}
                    </span>
                    <p class="text-xs text-slate-400 mt-2">Due Date: {{ date('M d, Y', strtotime($invoice->due_date)) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 text-xs">
                <div>
                    <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-1">Billed To</h4>
                    <p class="text-sm font-bold text-white">{{ $invoice->customer->name ?? 'Customer' }}</p>
                    <p class="text-slate-400">{{ $invoice->customer->address ?? '' }}</p>
                    <p class="text-slate-400">{{ $invoice->customer->city ?? '' }}, {{ $invoice->customer->country ?? '' }}</p>
                </div>
                <div class="text-right">
                    <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-1">Payable To</h4>
                    <p class="text-sm font-bold text-white">{{ $company->name ?? 'WebotApp Enterprise' }}</p>
                    <p class="text-slate-400">{{ $company->address ?? '' }}</p>
                    <p class="text-slate-400">Email: {{ $company->email ?? '' }}</p>
                </div>
            </div>

            <!-- Items -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-y border-slate-800 text-slate-400 font-semibold bg-slate-950/40">
                        <tr>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3 text-center">Qty</th>
                            <th class="py-2.5 px-3 text-right">Price</th>
                            <th class="py-2.5 px-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @foreach($invoice->items as $it)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-white">{{ $it->name }}</td>
                                <td class="py-2.5 px-3 text-center">{{ $it->quantity }}</td>
                                <td class="py-2.5 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->price, 2) }}</td>
                                <td class="py-2.5 px-3 text-right font-semibold text-white">{{ $currencySymbol }}{{ number_format($it->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Total -->
            <div class="flex justify-end pt-4 border-t border-slate-800">
                <div class="w-64 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Total Invoiced:</span>
                        <span class="text-white font-medium">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Paid:</span>
                        <span class="text-emerald-400 font-medium">{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-white pt-2 border-t border-slate-800">
                        <span>Amount Due:</span>
                        <span class="text-emerald-400 font-extrabold">{{ $currencySymbol }}{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($invoice->notes)
                <div class="pt-4 border-t border-slate-800 text-xs text-slate-400">
                    <h4 class="font-bold text-slate-300 mb-1">Payment Instructions:</h4>
                    <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>

        <p class="text-center text-xs text-slate-500">
            Powered by WebotApp Accounting Portal &bull; Verified Digital Bill
        </p>
    </div>
</body>
</html>
