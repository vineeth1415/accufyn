@extends('layouts.app')

@section('title', 'Products & Services')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Products & Services Catalog</h1>
            <p class="text-xs text-slate-400">Manage item pricing, sales rates, purchase costs, and tax settings</p>
        </div>
        <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-lg shadow-emerald-950 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Add Item</span>
        </button>
    </div>

    <!-- Items Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Item Name</th>
                        <th class="py-3.5 px-4">SKU</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4 text-right">Sale Price</th>
                        <th class="py-3.5 px-4 text-right">Purchase Price</th>
                        <th class="py-3.5 px-4 text-center">Tax</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($items as $it)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-white">
                                <p>{{ $it->name }}</p>
                                @if($it->description)
                                    <p class="text-[10px] text-slate-400 font-normal">{{ $it->description }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">{{ $it->sku ?? '—' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $it->category->name ?? 'General' }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-400">{{ $currencySymbol }}{{ number_format($it->sale_price, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-medium text-slate-400">{{ $currencySymbol }}{{ number_format($it->purchase_price, 2) }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($it->tax)
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300">{{ $it->tax->name }} ({{ $it->tax->rate }}%)</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border uppercase bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                    {{ $it->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" onclick="openEditItemModal({{ json_encode($it) }})" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 hover:text-emerald-700 transition" title="Edit Item">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('items.destroy', $it->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete product/service {{ $it->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" title="Delete Item">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">No items configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">New Product / Service</h3>
                <button onclick="document.getElementById('addItemModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('items.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Item Name *</label>
                    <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">SKU / Code</label>
                        <input type="text" name="sku" placeholder="e.g. PRD-001" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Category</label>
                        <select name="category_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">None</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Sales Price ({{ $currencySymbol }}) *</label>
                        <input type="number" name="sale_price" value="0.00" step="0.01" min="0" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Purchase Cost ({{ $currencySymbol }})</label>
                        <input type="number" name="purchase_price" value="0.00" step="0.01" min="0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax Rate</label>
                        <select name="tax_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">None (0%)</option>
                            @foreach($taxes as $tx)
                                <option value="{{ $tx->id }}">{{ $tx->name }} ({{ $tx->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Unit</label>
                        <input type="text" name="unit" placeholder="e.g. hrs, pcs, licenses" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addItemModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Save Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Edit Product / Service</h3>
                <button onclick="document.getElementById('editItemModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editItemForm" action="" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Item Name *</label>
                    <input type="text" name="name" id="edit_item_name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">SKU / Item Code</label>
                        <input type="text" name="sku" id="edit_item_sku" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Category</label>
                        <select name="category_id" id="edit_item_category_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">No Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Sales Price ({{ $currencySymbol }}) *</label>
                        <input type="number" name="sale_price" id="edit_item_sale_price" step="0.01" min="0" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Purchase Cost ({{ $currencySymbol }})</label>
                        <input type="number" name="purchase_price" id="edit_item_purchase_price" step="0.01" min="0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tax Rate</label>
                        <select name="tax_id" id="edit_item_tax_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                            <option value="">None (0%)</option>
                            @foreach($taxes as $tx)
                                <option value="{{ $tx->id }}">{{ $tx->name }} ({{ $tx->rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Unit</label>
                        <input type="text" name="unit" id="edit_item_unit" placeholder="e.g. hrs, pcs, licenses" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea name="description" id="edit_item_description" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editItemModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-950 transition">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditItemModal(item) {
        document.getElementById('edit_item_name').value = item.name || '';
        document.getElementById('edit_item_sku').value = item.sku || '';
        document.getElementById('edit_item_category_id').value = item.category_id || '';
        document.getElementById('edit_item_sale_price').value = parseFloat(item.sale_price || 0).toFixed(2);
        document.getElementById('edit_item_purchase_price').value = parseFloat(item.purchase_price || 0).toFixed(2);
        document.getElementById('edit_item_tax_id').value = item.tax_id || '';
        document.getElementById('edit_item_unit').value = item.unit || '';
        document.getElementById('edit_item_description').value = item.description || '';
        
        const form = document.getElementById('editItemForm');
        form.action = '{{ url("items") }}/' + item.id;

        document.getElementById('editItemModal').classList.remove('hidden');
    }
</script>
@endsection
