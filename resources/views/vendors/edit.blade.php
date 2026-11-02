@extends('layouts.app')

@section('title', 'Edit Vendor - ' . $vendor->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Vendor / Supplier</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Update supplier details, GSTIN, and address</p>
        </div>
        <a href="{{ route('vendors.index') }}" class="text-xs text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition">&larr; Back to Vendors</a>
    </div>

    <div class="glass-card p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <form action="{{ route('vendors.update', $vendor->id) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Supplier / Vendor Name *</label>
                    <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $vendor->email) }}" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">GSTIN / Tax ID</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $vendor->tax_number) }}" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Address</label>
                <input type="text" name="address" value="{{ old('address', $vendor->address) }}" placeholder="Street Address" 
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 mb-3 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="city" value="{{ old('city', $vendor->city) }}" placeholder="City" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                    <input type="text" name="country" value="{{ old('country', $vendor->country) }}" placeholder="Country" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-sm">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('vendors.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium rounded-xl transition">
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
