@extends('layouts.app')

@section('title', 'Edit Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Invoice #{{ $invoice->invoice_number }}</h1>
            <p class="text-xs text-slate-500">Update invoice details, item lines, taxes, and customer parameters</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-xs text-slate-500 hover:text-slate-800">&larr; Back to Invoice</a>
        </div>
    </div>

    <!-- Datalist for Fast Item Autocomplete -->
    <datalist id="catalogItemsList">
        @foreach($items as $it)
            <option value="{{ $it->name }}" data-price="{{ $it->sale_price }}" data-tax-rate="{{ $it->tax->rate ?? 0 }}">
                {{ $it->sku ? '[' . $it->sku . '] ' : '' }}{{ $it->name }} &bull; {{ $currencySymbol }}{{ number_format($it->sale_price, 2) }}
            </option>
        @endforeach
    </datalist>

    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" id="invoiceForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Top Details Card -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Customer *</label>
                        <button type="button" onclick="openNewCustomerModal()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> + New Customer
                        </button>
                    </div>
                    <select id="customer_select" name="customer_id" required class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (old('customer_id', $invoice->customer_id) == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email ?: 'No email' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Invoice Number *</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-mono font-bold text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Status *</label>
                    <select name="status" class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                        <option value="pending" {{ old('status', $invoice->status) == 'pending' ? 'selected' : '' }}>PENDING</option>
                        <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>SENT (Emails Customer)</option>
                        <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>PAID</option>
                        <option value="partial" {{ old('status', $invoice->status) == 'partial' ? 'selected' : '' }}>PARTIAL</option>
                        <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>DRAFT</option>
                        <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Invoice Date *</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d', strtotime($invoice->invoice_date))) }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime($invoice->due_date))) }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </div>
        </div>

        <!-- Line Items Card -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Line Items</h3>
                    <p class="text-[11px] text-slate-500">Pick catalog items or customize descriptions, pricing, and taxes</p>
                </div>
                <button type="button" onclick="addRow()" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-bold px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 shadow-sm transition">
                    <i class="fa-solid fa-plus text-emerald-600"></i>
                    <span>Add Item</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="itemsTable">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 min-w-[280px]">Product / Service (Catalog & Custom)</th>
                            <th class="py-2.5 px-3 w-24">Qty</th>
                            <th class="py-2.5 px-3 w-32">Price ({{ $currencySymbol }})</th>
                            <th class="py-2.5 px-3 w-32">Tax (GST)</th>
                            <th class="py-2.5 px-3 w-32 text-right">Line Total</th>
                            <th class="py-2.5 px-2 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100">
                        @forelse($invoice->items as $index => $item)
                            <tr class="item-row">
                                <td class="py-2.5 px-3">
                                    <div class="space-y-1.5">
                                        <select onchange="onCatalogItemSelect(this)" class="catalog-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 text-xs font-medium focus:outline-none focus:border-emerald-600">
                                            <option value="">-- Choose from Catalog (or edit below) --</option>
                                            @foreach($items as $it)
                                                <option value="{{ $it->id }}" 
                                                    {{ ($item->item_id == $it->id) ? 'selected' : '' }}
                                                    data-name="{{ $it->name }}" 
                                                    data-price="{{ $it->sale_price }}" 
                                                    data-tax-rate="{{ $it->tax->rate ?? 0 }}">
                                                    {{ $it->name }} {{ $it->sku ? '(' . $it->sku . ')' : '' }} &bull; {{ $currencySymbol }}{{ number_format($it->sale_price, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="items[{{ $index }}][name]" list="catalogItemsList" required value="{{ $item->name }}" placeholder="Service or Product description"
                                            oninput="onItemNameInput(this)"
                                            class="item-name-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                                        @if($item->item_id)
                                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}" class="item-id-hidden">
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="0.01" step="any" required oninput="calcTotals()"
                                        class="qty-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-semibold text-center focus:outline-none focus:border-emerald-600 shadow-sm">
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <input type="number" name="items[{{ $index }}][price]" value="{{ number_format($item->price, 2, '.', '') }}" min="0" step="0.01" required oninput="calcTotals()"
                                        class="price-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-600 shadow-sm">
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <select name="items[{{ $index }}][tax_rate]" onchange="calcTotals()" class="tax-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm">
                                        <option value="0" {{ ($item->tax_rate == 0) ? 'selected' : '' }}>0% None</option>
                                        @foreach($taxes as $tx)
                                            <option value="{{ $tx->rate }}" {{ (abs($item->tax_rate - $tx->rate) < 0.01) ? 'selected' : '' }}>{{ $tx->name }} ({{ $tx->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2.5 px-3 align-top pt-4 text-right font-extrabold text-slate-900 line-total">{{ $currencySymbol }}{{ number_format($item->total, 2) }}</td>
                                <td class="py-2.5 px-2 align-top pt-3.5 text-center">
                                    <button type="button" onclick="removeRow(this)" class="text-slate-400 hover:text-red-600 transition p-1"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr class="item-row">
                                <td class="py-2.5 px-3">
                                    <div class="space-y-1.5">
                                        <select onchange="onCatalogItemSelect(this)" class="catalog-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 text-xs font-medium focus:outline-none focus:border-emerald-600">
                                            <option value="">-- Choose from Catalog (or type below) --</option>
                                            @foreach($items as $it)
                                                <option value="{{ $it->id }}" 
                                                    data-name="{{ $it->name }}" 
                                                    data-price="{{ $it->sale_price }}" 
                                                    data-tax-rate="{{ $it->tax->rate ?? 0 }}">
                                                    {{ $it->name }} {{ $it->sku ? '(' . $it->sku . ')' : '' }} &bull; {{ $currencySymbol }}{{ number_format($it->sale_price, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="items[0][name]" list="catalogItemsList" required placeholder="Service or Product description"
                                            oninput="onItemNameInput(this)"
                                            class="item-name-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <input type="number" name="items[0][quantity]" value="1" min="0.01" step="any" required oninput="calcTotals()"
                                        class="qty-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-semibold text-center focus:outline-none focus:border-emerald-600 shadow-sm">
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <input type="number" name="items[0][price]" value="0.00" min="0" step="0.01" required oninput="calcTotals()"
                                        class="price-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-600 shadow-sm">
                                </td>
                                <td class="py-2.5 px-3 align-top pt-3">
                                    <select name="items[0][tax_rate]" onchange="calcTotals()" class="tax-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm">
                                        <option value="0">0% None</option>
                                        @foreach($taxes as $tx)
                                            <option value="{{ $tx->rate }}">{{ $tx->name }} ({{ $tx->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2.5 px-3 align-top pt-4 text-right font-extrabold text-slate-900 line-total">{{ $currencySymbol }}0.00</td>
                                <td class="py-2.5 px-2 align-top pt-3.5 text-center">
                                    <button type="button" onclick="removeRow(this)" class="text-slate-400 hover:text-red-600 transition p-1"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Financial Totals Block -->
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <div class="w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal:</span>
                        <span id="subtotalDisplay" class="text-slate-900 font-bold">{{ $currencySymbol }}0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Taxes (GST/VAT):</span>
                        <span id="taxDisplay" class="text-slate-900 font-bold">{{ $currencySymbol }}0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500 items-center">
                        <span>Discount:</span>
                        <div class="w-28">
                            <input type="number" name="discount_total" id="discountInput" value="{{ number_format($invoice->discount_total ?? 0, 2, '.', '') }}" min="0" step="0.01" oninput="calcTotals()"
                                class="w-full bg-white border border-slate-300 rounded-lg px-2 py-1 text-slate-900 text-right text-xs font-semibold focus:outline-none focus:border-emerald-600 shadow-sm">
                        </div>
                    </div>
                    <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Payable:</span>
                        <span id="grandTotalDisplay" class="text-emerald-700 font-black">{{ $currencySymbol }}0.00</span>
                    </div>
                    @if($invoice->paid_amount > 0)
                        <div class="flex justify-between text-xs text-slate-500 pt-1">
                            <span>Already Paid:</span>
                            <span class="text-emerald-600 font-semibold">{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes & Terms -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Customer Notes / Payment Instructions</label>
                <textarea name="notes" rows="2" placeholder="Bank details, NEFT/RTGS/UPI instructions, or thank you message..."
                    class="w-full bg-white border border-slate-300 rounded-xl p-3 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Terms & Conditions</label>
                <textarea name="terms" rows="2" placeholder="Payment terms, late fee conditions..."
                    class="w-full bg-white border border-slate-300 rounded-xl p-3 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm">{{ old('terms', $invoice->terms) }}</textarea>
            </div>
        </div>

        <!-- Submission Buttons -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('invoices.show', $invoice->id) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-check text-white"></i>
                <span class="text-white force-white">Update Invoice</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let rowIndex = {{ count($invoice->items) > 0 ? count($invoice->items) : 1 }};
    const taxes = {!! json_encode($taxes) !!};
    const catalogItems = {!! json_encode($items) !!};
    const currencySymbol = {!! json_encode($currencySymbol) !!};

    function onCatalogItemSelect(selectElem) {
        const row = selectElem.closest('.item-row');
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const name = selectedOption.getAttribute('data-name');
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;

        const nameInput = row.querySelector('.item-name-input');
        const priceInput = row.querySelector('.price-input');
        const taxSelect = row.querySelector('.tax-input');

        if (nameInput) nameInput.value = name;
        if (priceInput) priceInput.value = price.toFixed(2);

        if (taxSelect) {
            let matched = false;
            for (let opt of taxSelect.options) {
                if (parseFloat(opt.value) === taxRate) {
                    opt.selected = true;
                    matched = true;
                    break;
                }
            }
            if (!matched && taxSelect.options.length > 0) {
                taxSelect.options[0].selected = true;
            }
        }

        calcTotals();
    }

    function onItemNameInput(inputElem) {
        const val = inputElem.value.trim().toLowerCase();
        const matchedItem = catalogItems.find(it => it.name.toLowerCase() === val || (it.sku && it.sku.toLowerCase() === val));
        if (matchedItem) {
            const row = inputElem.closest('.item-row');
            const priceInput = row.querySelector('.price-input');
            const taxSelect = row.querySelector('.tax-input');
            const catalogSelect = row.querySelector('.catalog-select');

            if (priceInput) priceInput.value = parseFloat(matchedItem.sale_price).toFixed(2);
            if (catalogSelect) catalogSelect.value = matchedItem.id;

            if (taxSelect && matchedItem.tax) {
                for (let opt of taxSelect.options) {
                    if (parseFloat(opt.value) === parseFloat(matchedItem.tax.rate)) {
                        opt.selected = true;
                        break;
                    }
                }
            }
            calcTotals();
        }
    }

    function addRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row';

        let taxOptions = '<option value="0">0% None</option>';
        taxes.forEach(t => {
            taxOptions += `<option value="${t.rate}">${t.name} (${t.rate}%)</option>`;
        });

        let catalogOptions = '<option value="">-- Choose from Catalog (or type below) --</option>';
        catalogItems.forEach(it => {
            catalogOptions += `<option value="${it.id}" data-name="${it.name}" data-price="${it.sale_price}" data-tax-rate="${it.tax ? it.tax.rate : 0}">${it.name} &bull; ${currencySymbol}${parseFloat(it.sale_price).toFixed(2)}</option>`;
        });

        tr.innerHTML = `
            <td class="py-2.5 px-3">
                <div class="space-y-1.5">
                    <select onchange="onCatalogItemSelect(this)" class="catalog-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 text-xs font-medium focus:outline-none focus:border-emerald-600">
                        ${catalogOptions}
                    </select>
                    <input type="text" name="items[${rowIndex}][name]" list="catalogItemsList" required placeholder="Service or Product description"
                        oninput="onItemNameInput(this)"
                        class="item-name-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </td>
            <td class="py-2.5 px-3 align-top pt-3">
                <input type="number" name="items[${rowIndex}][quantity]" value="1" min="0.01" step="any" required oninput="calcTotals()"
                    class="qty-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-semibold text-center focus:outline-none focus:border-emerald-600 shadow-sm">
            </td>
            <td class="py-2.5 px-3 align-top pt-3">
                <input type="number" name="items[${rowIndex}][price]" value="0.00" min="0" step="0.01" required oninput="calcTotals()"
                    class="price-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs font-bold focus:outline-none focus:border-emerald-600 shadow-sm">
            </td>
            <td class="py-2.5 px-3 align-top pt-3">
                <select name="items[${rowIndex}][tax_rate]" onchange="calcTotals()" class="tax-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm">
                    ${taxOptions}
                </select>
            </td>
            <td class="py-2.5 px-3 align-top pt-4 text-right font-extrabold text-slate-900 line-total">${currencySymbol}0.00</td>
            <td class="py-2.5 px-2 align-top pt-3.5 text-center">
                <button type="button" onclick="removeRow(this)" class="text-slate-400 hover:text-red-600 transition p-1"><i class="fa-solid fa-trash-can"></i></button>
            </td>
        `;

        tbody.appendChild(tr);
        rowIndex++;
        calcTotals();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('An invoice must have at least one line item.');
            return;
        }
        btn.closest('.item-row').remove();
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
            const taxRate = parseFloat(row.querySelector('.tax-input')?.value) || 0;

            const lineSub = qty * price;
            const lineTax = lineSub * (taxRate / 100);
            const lineGrand = lineSub + lineTax;

            subtotal += lineSub;
            totalTax += lineTax;

            const totalCell = row.querySelector('.line-total');
            if (totalCell) {
                totalCell.textContent = currencySymbol + lineGrand.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        });

        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const grandTotal = Math.max(0, (subtotal + totalTax) - discount);

        document.getElementById('subtotalDisplay').textContent = currencySymbol + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('taxDisplay').textContent = currencySymbol + totalTax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('grandTotalDisplay').textContent = currencySymbol + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Initialize calculation on page load
    document.addEventListener('DOMContentLoaded', function() {
        calcTotals();
    });

    // Quick Customer Modal
    function openNewCustomerModal() {
        document.getElementById('newCustomerModal').classList.remove('hidden');
        document.getElementById('qc_name')?.focus();
    }

    function closeNewCustomerModal() {
        document.getElementById('newCustomerModal').classList.add('hidden');
        document.getElementById('quickCustomerForm')?.reset();
        document.getElementById('quickCustomerError')?.classList.add('hidden');
    }

    async function handleQuickCustomerSubmit(e) {
        e.preventDefault();
        const errDiv = document.getElementById('quickCustomerError');
        const btn = document.getElementById('qc_submit_btn');
        errDiv.classList.add('hidden');
        errDiv.textContent = '';
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Saving...</span>';

        const payload = {
            name: document.getElementById('qc_name').value.trim(),
            email: document.getElementById('qc_email').value.trim() || null,
            phone: document.getElementById('qc_phone').value.trim() || null,
            company_name: document.getElementById('qc_company_name').value.trim() || null,
            tax_number: document.getElementById('qc_tax_number').value.trim() || null,
            address: document.getElementById('qc_address').value.trim() || null,
            city: document.getElementById('qc_city').value.trim() || null,
            country: document.getElementById('qc_country').value.trim() || 'India'
        };

        try {
            const res = await fetch("{{ route('customers.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success && data.customer) {
                const select = document.getElementById('customer_select');
                const newOption = document.createElement('option');
                newOption.value = data.customer.id;
                newOption.textContent = data.customer.name + (data.customer.email ? ' (' + data.customer.email + ')' : '');
                newOption.selected = true;
                select.appendChild(newOption);
                select.value = data.customer.id;

                closeNewCustomerModal();
            } else {
                errDiv.textContent = data.message || 'Could not save customer. Please verify inputs.';
                errDiv.classList.remove('hidden');
            }
        } catch (err) {
            errDiv.textContent = 'Server connection error: ' + err.message;
            errDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Save Customer</span>';
        }
    }
</script>

<!-- Quick Create New Customer Modal -->
<div id="newCustomerModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add New Customer</h3>
            </div>
            <button type="button" onclick="closeNewCustomerModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="quickCustomerForm" onsubmit="handleQuickCustomerSubmit(event)" class="space-y-3.5 text-xs">
            @csrf
            <div id="quickCustomerError" class="hidden p-2.5 rounded-xl bg-red-50 text-red-600 border border-red-200 text-xs"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Customer / Client Name *</label>
                    <input type="text" name="name" id="qc_name" required placeholder="e.g. John Doe or Apex Global"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" name="email" id="qc_email" placeholder="client@example.com"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Phone Number</label>
                    <input type="text" name="phone" id="qc_phone" placeholder="+91 9876543210"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Company / Business Name</label>
                    <input type="text" name="company_name" id="qc_company_name" placeholder="Acme Logistics Ltd"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">GSTIN / Tax ID</label>
                    <input type="text" name="tax_number" id="qc_tax_number" placeholder="18AABCT2345K1Z5"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Billing Address</label>
                    <input type="text" name="address" id="qc_address" placeholder="Street, Building, Area"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City</label>
                    <input type="text" name="city" id="qc_city" placeholder="e.g. Guwahati / Mumbai"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                    <input type="text" name="country" id="qc_country" value="India"
                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
                <button type="button" onclick="closeNewCustomerModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" id="qc_submit_btn" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Customer</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
