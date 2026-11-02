<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Bill - {{ $bill->bill_number }}</title>
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
            <h1 class="text-xl font-bold tracking-tight text-slate-950">{{ $company->name ?? 'WebotApp Enterprise' }}</h1>
            <p class="text-xs text-slate-600 mt-1">{{ $company->address ?? '' }}</p>
            <p class="text-xs text-slate-600">{{ $company->city ?? '' }}, {{ $company->country ?? '' }}</p>
            <p class="text-xs text-slate-600">Email: {{ $company->email ?? '' }}</p>
            @if(!empty($company->tax_number))
                <p class="text-xs text-slate-600">Tax ID: {{ $company->tax_number }}</p>
            @endif
        </div>
        <div class="text-right">
            <h2 class="text-2xl font-black uppercase text-slate-900">VENDOR BILL</h2>
            <p class="text-sm font-mono font-bold text-slate-700 mt-1">{{ $bill->bill_number }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Date: {{ date('M d, Y', strtotime($bill->bill_date)) }}</p>
            <p class="text-xs text-slate-500">Due: {{ date('M d, Y', strtotime($bill->due_date)) }}</p>
            @if($bill->order_number)
                <p class="text-xs text-slate-500">Ref #: {{ $bill->order_number }}</p>
            @endif
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-xs font-bold text-slate-400 uppercase mb-1">Vendor (Payable To)</h3>
        <h4 class="text-sm font-bold text-slate-900">{{ $bill->vendor->name ?? 'Vendor' }}</h4>
        <p class="text-xs text-slate-600">{{ $bill->vendor->address ?? '' }}</p>
        <p class="text-xs text-slate-600">{{ $bill->vendor->city ?? '' }}, {{ $bill->vendor->country ?? '' }}</p>
        <p class="text-xs text-slate-600">Email: {{ $bill->vendor->email ?? '' }}</p>
        <p class="text-xs text-slate-600">Phone: {{ $bill->vendor->phone ?? '' }}</p>
        @if(!empty($bill->vendor->tax_number))
            <p class="text-xs text-slate-600">Vendor GST/Tax ID: {{ $bill->vendor->tax_number }}</p>
        @endif
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
            @foreach($bill->items as $it)
                <tr>
                    <td class="py-2.5 px-3 font-medium text-slate-800">{{ $it->name }}</td>
                    <td class="py-2.5 px-3 text-center text-slate-600">{{ $it->quantity }}</td>
                    <td class="py-2.5 px-3 text-right text-slate-600">{{ $currencySymbol }}{{ number_format($it->price, 2) }}</td>
                    <td class="py-2.5 px-3 text-right text-slate-600">{{ $it->tax_rate }}%</td>
                    <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($it->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-end mb-8">
        <div class="w-64 space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($bill->subtotal ?? ($bill->amount ?? 0), 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Tax Total:</span>
                <span class="font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($bill->tax_total ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between border-t border-slate-300 pt-2 text-sm font-black text-slate-950">
                <span>Total Amount:</span>
                <span>{{ $currencySymbol }}{{ number_format($bill->total_amount ?? $bill->total, 2) }}</span>
            </div>
            <div class="flex justify-between text-xs text-emerald-700 font-semibold pt-1">
                <span>Paid Amount:</span>
                <span>{{ $currencySymbol }}{{ number_format($bill->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-xs text-red-700 font-bold">
                <span>Due Amount:</span>
                <span>{{ $currencySymbol }}{{ number_format($bill->due_amount, 2) }}</span>
            </div>
        </div>
    </div>

    @if(!empty($bill->notes))
        <div class="border-t border-slate-200 pt-4 text-xs text-slate-500">
            <h4 class="font-bold text-slate-700 mb-1">Notes / Instructions:</h4>
            <p>{{ $bill->notes }}</p>
        </div>
    @endif
</body>
</html>
