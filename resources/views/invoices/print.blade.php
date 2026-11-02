<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Invoice - {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 p-8 max-w-4xl mx-auto text-sm">
    <div class="no-print mb-6 flex justify-between items-center bg-slate-100 p-4 rounded-xl border border-slate-200">
        <span class="text-xs text-slate-600">Print Preview Mode</span>
        <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg text-xs shadow hover:bg-emerald-500 transition">
            Print / Save as PDF
        </button>
    </div>

    <div class="flex justify-between items-start border-b pb-6 mb-6">
        <div>
            @if(!empty($company->logo_path))
                <img src="{{ asset(ltrim($company->logo_path, '/')) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="h-14 w-auto max-h-14 max-w-[200px] object-contain mb-3" onerror="this.style.display='none'">
            @endif
            <h1 class="text-xl font-bold tracking-tight text-slate-950">{{ $company->name ?? 'WebotApp Enterprise' }}</h1>
            <p class="text-xs text-slate-600 mt-1">{{ $company->address ?? '' }}</p>
            <p class="text-xs text-slate-600">{{ $company->city ?? '' }}, {{ $company->country ?? '' }}</p>
            <p class="text-xs text-slate-600">Email: {{ $company->email ?? '' }}</p>
            @if(!empty($company->tax_number))
                <p class="text-xs text-slate-600">Tax ID: {{ $company->tax_number }}</p>
            @endif
        </div>
        <div class="text-right">
            <h2 class="text-2xl font-black uppercase text-slate-900">INVOICE</h2>
            <p class="text-sm font-mono font-bold text-slate-700 mt-1">{{ $invoice->invoice_number }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Date: {{ date('M d, Y', strtotime($invoice->invoice_date)) }}</p>
            <p class="text-xs text-slate-500">Due: {{ date('M d, Y', strtotime($invoice->due_date)) }}</p>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-xs font-bold text-slate-400 uppercase mb-1">Billed To</h3>
        <h4 class="text-sm font-bold text-slate-900">{{ $invoice->customer->name ?? 'Customer' }}</h4>
        <p class="text-xs text-slate-600">{{ $invoice->customer->address ?? '' }}</p>
        <p class="text-xs text-slate-600">{{ $invoice->customer->city ?? '' }}, {{ $invoice->customer->country ?? '' }}</p>
        <p class="text-xs text-slate-600">Email: {{ $invoice->customer->email ?? '' }}</p>
    </div>

    <table class="w-full text-left text-xs mb-6">
        <thead class="border-y border-slate-300 font-bold bg-slate-50">
            <tr>
                <th class="py-2.5 px-3">Description</th>
                <th class="py-2.5 px-3 text-center">Qty</th>
                <th class="py-2.5 px-3 text-right">Price</th>
                <th class="py-2.5 px-3 text-right">Tax</th>
                <th class="py-2.5 px-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($invoice->items as $it)
                <tr>
                    <td class="py-2.5 px-3 font-medium">{{ $it->name }}</td>
                    <td class="py-2.5 px-3 text-center">{{ $it->quantity }}</td>
                    <td class="py-2.5 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->price, 2) }}</td>
                    <td class="py-2.5 px-3 text-right">{{ $currencySymbol }}{{ number_format($it->tax_amount, 2) }}</td>
                    <td class="py-2.5 px-3 text-right font-bold">{{ $currencySymbol }}{{ number_format($it->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-end mb-8">
        <div class="w-64 space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Tax:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t">
                <span>Total:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Paid:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm font-bold text-slate-900 pt-1 border-t">
                <span>Balance Due:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</span>
            </div>
        </div>
    </div>

    @if($invoice->notes)
        <div class="border-t pt-4 text-xs text-slate-600">
            <h4 class="font-bold text-slate-800 mb-1">Notes:</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif
</body>
</html>
