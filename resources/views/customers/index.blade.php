@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Customers</h1>
            <p class="text-xs text-slate-400">Manage client contacts, billing addresses, and accounts receivable</p>
        </div>
        <button onclick="document.getElementById('addCustomerModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-lg shadow-emerald-950 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Add Customer</span>
        </button>
    </div>

    <!-- Customers List -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Name</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4 text-right">Invoices</th>
                        <th class="py-3.5 px-4 text-right">Outstanding</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-white">
                                <a href="{{ route('customers.show', $c->id) }}" class="hover:text-emerald-400 transition">{{ $c->name }}</a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $c->email }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $c->phone ?? '—' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $c->city ? $c->city . ', ' . $c->country : '—' }}</td>
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-300">{{ $c->invoices_count }}</td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $c->outstanding_balance > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                                {{ $company->currency_symbol ?? ($currencySymbol ?? '₹') }}{{ number_format($c->outstanding_balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <a href="{{ route('customers.show', $c->id) }}" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 transition" title="View Customer">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button type="button" onclick="openEditModal({{ json_encode($c) }})" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" title="Edit Customer">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete customer {{ $c->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Customer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">No customers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">New Customer</h3>
                <button onclick="document.getElementById('addCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company / Name *</label>
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
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax / VAT Number</label>
                        <input type="text" name="tax_number" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Billing Address</label>
                    <input type="text" name="address" placeholder="Street Address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 mb-2">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="city" placeholder="City" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <input type="text" name="country" placeholder="Country" value="United States" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addCustomerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Edit Customer</h3>
                <button onclick="document.getElementById('editCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editCustomerForm" action="" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company / Name *</label>
                        <input type="text" name="name" id="edit_name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax / VAT Number</label>
                        <input type="text" name="tax_number" id="edit_tax_number" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Billing Address</label>
                    <input type="text" name="address" id="edit_address" placeholder="Street Address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 mb-2">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="city" id="edit_city" placeholder="City" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <input type="text" name="country" id="edit_country" placeholder="Country" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editCustomerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(customer) {
        document.getElementById('edit_name').value = customer.name || '';
        document.getElementById('edit_email').value = customer.email || '';
        document.getElementById('edit_phone').value = customer.phone || '';
        document.getElementById('edit_tax_number').value = customer.tax_number || '';
        document.getElementById('edit_address').value = customer.address || '';
        document.getElementById('edit_city').value = customer.city || '';
        document.getElementById('edit_country').value = customer.country || '';
        
        const form = document.getElementById('editCustomerForm');
        form.action = '{{ url("customers") }}/' + customer.id;

        document.getElementById('editCustomerModal').classList.remove('hidden');
    }
</script>
@endsection
