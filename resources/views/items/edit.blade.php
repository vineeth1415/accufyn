@extends('layouts.app')

@section('title', 'Edit Item - ' . $item->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Product / Service</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update item pricing, category, HSN/SAC code, and tax settings</p>
        </div>
        <a href="{{ route('items.index') }}" class="text-xs text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition">&larr; Back to Catalog</a>
    </div>

    <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <form action="{{ route('items.update', $item->id) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Item Name *</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">SKU / Code / HSN</label>
                    <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 font-mono shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Selling Price ({{ $currencySymbol }}) *</label>
                    <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $item->sale_price) }}" required 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white font-bold focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Purchase / Cost Price</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $item->purchase_price) }}" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Unit</label>
                    <input type="text" name="unit" value="{{ old('unit', $item->unit ?: 'pcs') }}" placeholder="e.g. pcs, hrs, kg" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Category</label>
                    <select name="category_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                        <option value="">None / General</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Applicable Tax Rate</label>
                    <select name="tax_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                        <option value="">No Tax (0%)</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->id }}" {{ old('tax_id', $item->tax_id) == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Description / Specifications</label>
                <textarea name="description" rows="3" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">{{ old('description', $item->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('items.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
