@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Vendors & Suppliers</h1>
            <p class="text-xs text-slate-400">Manage supplier profiles, purchase bills, and accounts payable</p>
        </div>
        <button onclick="document.getElementById('addVendorModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-lg shadow-emerald-950 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Add Vendor</span>
        </button>
    </div>

    <!-- Vendors List -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Vendor Name</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4 text-right">Bills</th>
                        <th class="py-3.5 px-4 text-right">Payable Balance</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($vendors as $v)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-white">
                                <a href="{{ route('vendors.show', $v->id) }}" class="hover:text-emerald-400 transition">{{ $v->name }}</a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $v->email }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $v->phone ?? '—' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $v->city ? $v->city . ', ' . $v->country : '—' }}</td>
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-300">{{ $v->bills_count }}</td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $v->outstanding_balance > 0 ? 'text-red-400' : 'text-slate-400' }}">
                                {{ $company->currency_symbol ?? ($currencySymbol ?? '₹') }}{{ number_format($v->outstanding_balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('vendors.show', $v->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="View Vendor">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button type="button" onclick="openEditVendorModal({{ json_encode($v) }})" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" title="Edit Vendor">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('vendors.destroy', $v->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete vendor {{ $v->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Vendor">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">No vendors registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

    <!-- Add Vendor Modal -->
    <div id="addVendorModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">New Vendor</h3>
                <button onclick="document.getElementById('addVendorModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('vendors.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company / Vendor Name *</label>
                        <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                        <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax Number</label>
                        <input type="text" name="tax_number" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Address</label>
                    <input type="text" name="address" placeholder="Street Address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 mb-2">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="city" placeholder="City" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <input type="text" name="country" placeholder="Country" value="United States" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addVendorModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Save Vendor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Vendor Modal -->
    <div id="editVendorModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Edit Vendor</h3>
                <button onclick="document.getElementById('editVendorModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editVendorForm" action="" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company / Vendor Name *</label>
                        <input type="text" name="name" id="edit_vendor_name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" id="edit_vendor_email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" id="edit_vendor_phone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax / GST Number</label>
                        <input type="text" name="tax_number" id="edit_vendor_tax_number" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Address</label>
                    <input type="text" name="address" id="edit_vendor_address" placeholder="Street Address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 mb-2">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="city" id="edit_vendor_city" placeholder="City" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <input type="text" name="country" id="edit_vendor_country" placeholder="Country" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editVendorModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Update Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditVendorModal(vendor) {
        document.getElementById('edit_vendor_name').value = vendor.name || '';
        document.getElementById('edit_vendor_email').value = vendor.email || '';
        document.getElementById('edit_vendor_phone').value = vendor.phone || '';
        document.getElementById('edit_vendor_tax_number').value = vendor.tax_number || '';
        document.getElementById('edit_vendor_address').value = vendor.address || '';
        document.getElementById('edit_vendor_city').value = vendor.city || '';
        document.getElementById('edit_vendor_country').value = vendor.country || '';
        
        const form = document.getElementById('editVendorForm');
        form.action = '{{ url("vendors") }}/' + vendor.id;

        document.getElementById('editVendorModal').classList.remove('hidden');
    }
</script>
@endsection
