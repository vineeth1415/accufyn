@extends('layouts.app')

@section('title', 'New Vendor Bill')

@php
    $currencySymbol = $company->currency_symbol ?? '₹';
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Vendor Bill</h1>
            <p class="text-xs text-slate-500">Record a purchase bill or operating expense from a vendor</p>
        </div>
        <a href="{{ route('bills.index') }}" class="text-xs text-slate-500 hover:text-slate-800">&larr; Back to Bills</a>
    </div>

    <!-- Datalist for Fast Item Autocomplete -->
    <datalist id="billCatalogItemsList">
        @foreach($items as $it)
            <option value="{{ $it->name }}" data-price="{{ $it->purchase_price ?? 0 }}" data-tax-rate="{{ $it->tax->rate ?? 0 }}">
                {{ $it->sku ? '[' . $it->sku . '] ' : '' }}{{ $it->name }} &bull; {{ $currencySymbol }}{{ number_format($it->purchase_price ?? 0, 2) }}
            </option>
        @endforeach
    </datalist>

    <form action="{{ route('bills.store') }}" method="POST" id="billForm" class="space-y-6">
        @csrf

        <!-- Top Details Card -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Vendor *</label>
                    <select name="vendor_id" required class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Bill Number *</label>
                    <input type="text" name="bill_number" value="{{ old('bill_number', $nextBillNumber) }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 font-mono text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Vendor Ref / Invoice #</label>
                    <input type="text" name="order_number" placeholder="e.g. VEN-INV-541"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Bill Date *</label>
                    <input type="date" name="bill_date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Due Date *</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Expense Category *</label>
                    <select name="category_id" required class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                        <option value="">Select Expense Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Line Items Card -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Expense Line Items</h3>
                    <p class="text-[11px] text-slate-500">Choose catalog products/services or enter custom purchase details</p>
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
                            <th class="py-2.5 px-3 w-32">Cost ({{ $currencySymbol }})</th>
                            <th class="py-2.5 px-3 w-32">Tax (GST)</th>
                            <th class="py-2.5 px-3 w-32 text-right">Line Total</th>
                            <th class="py-2.5 px-2 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100">
                        <tr class="item-row">
                            <td class="py-2.5 px-3">
                                <div class="space-y-1.5">
                                    <select onchange="onCatalogItemSelect(this)" class="catalog-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 text-xs font-medium focus:outline-none focus:border-emerald-600">
                                        <option value="">-- Choose from Catalog (or type below) --</option>
                                        @foreach($items as $it)
                                            <option value="{{ $it->id }}" 
                                                data-name="{{ $it->name }}" 
                                                data-price="{{ $it->purchase_price ?? 0 }}" 
                                                data-tax-rate="{{ $it->tax->rate ?? 0 }}">
                                                {{ $it->name }} {{ $it->sku ? '(' . $it->sku . ')' : '' }} &bull; {{ $currencySymbol }}{{ number_format($it->purchase_price ?? 0, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="items[0][item_name]" list="billCatalogItemsList" required placeholder="Service or Purchased item"
                                        oninput="onItemNameInput(this)"
                                        class="item-name-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                                </div>
                            </td>
                            <td class="py-2.5 px-3 align-top pt-3">
                                <input type="number" name="items[0][quantity]" value="1" min="1" step="1" required oninput="calcTotals()"
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
                        <span>Tax Total:</span>
                        <span id="taxDisplay" class="text-slate-900 font-bold">{{ $currencySymbol }}0.00</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Payable:</span>
                        <span id="grandTotalDisplay" class="text-emerald-700 font-black">{{ $currencySymbol }}0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-sm bg-white space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Notes & Remarks</label>
                <textarea name="notes" rows="2" placeholder="Internal notes or memo..."
                    class="w-full bg-white border border-slate-300 rounded-xl p-3 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 shadow-sm"></textarea>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('bills.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <i class="fa-solid fa-check text-white"></i>
                <span class="text-white force-white">Save Vendor Bill</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;
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

            const costPrice = parseFloat(matchedItem.purchase_price || 0);
            if (priceInput) priceInput.value = costPrice.toFixed(2);
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

        let itemOptions = '<option value="">-- Choose from Catalog (or type below) --</option>';
        catalogItems.forEach(it => {
            const priceStr = parseFloat(it.purchase_price || 0).toFixed(2);
            const skuStr = it.sku ? `(${it.sku}) ` : '';
            itemOptions += `<option value="${it.id}" data-name="${it.name}" data-price="${it.purchase_price || 0}" data-tax-rate="${it.tax ? it.tax.rate : 0}">${it.name} ${skuStr}&bull; ${currencySymbol}${priceStr}</option>`;
        });

        tr.innerHTML = `
            <td class="py-2.5 px-3">
                <div class="space-y-1.5">
                    <select onchange="onCatalogItemSelect(this)" class="catalog-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 text-xs font-medium focus:outline-none focus:border-emerald-600">
                        ${itemOptions}
                    </select>
                    <input type="text" name="items[${rowIndex}][item_name]" list="billCatalogItemsList" required placeholder="Service or Purchased item"
                        oninput="onItemNameInput(this)"
                        class="item-name-input w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-900 text-xs focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 shadow-sm">
                </div>
            </td>
            <td class="py-2.5 px-3 align-top pt-3">
                <input type="number" name="items[${rowIndex}][quantity]" value="1" min="1" step="1" required oninput="calcTotals()"
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
        if (rows.length > 1) {
            btn.closest('tr').remove();
            calcTotals();
        } else {
            alert('Bill must contain at least one line item.');
        }
    }

    function calcTotals() {
        let subtotal = 0;
        let taxTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;

            const lineSub = qty * price;
            const lineTax = (lineSub * taxRate) / 100;
            const lineTotal = lineSub + lineTax;

            row.querySelector('.line-total').innerText = currencySymbol + lineTotal.toFixed(2);

            subtotal += lineSub;
            taxTotal += lineTax;
        });

        const grandTotal = subtotal + taxTotal;

        document.getElementById('subtotalDisplay').innerText = currencySymbol + subtotal.toFixed(2);
        document.getElementById('taxDisplay').innerText = currencySymbol + taxTotal.toFixed(2);
        document.getElementById('grandTotalDisplay').innerText = currencySymbol + grandTotal.toFixed(2);
    }

    calcTotals();
</script>
@endsection
