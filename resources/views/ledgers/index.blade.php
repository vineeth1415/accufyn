@extends('layouts.app')

@section('title', 'Custom Ledgers')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Custom Ledgers &amp; Chart of Accounts</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">General Ledger</span>
            </h1>
            <p class="text-xs text-slate-400">Create, organize, and reconcile general ledger accounts, opening balances, and debits/credits</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.trial_balance') }}" 
                class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 transition">
                <i class="fa-solid fa-scale-balanced text-slate-500"></i>
                <span>Trial Balance</span>
            </a>

            @if(Auth::user()->canManageLedgers())
                <button onclick="document.getElementById('addLedgerModal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-plus text-white"></i>
                    <span class="text-white force-white">Create Custom Ledger</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-slate-500">Total Ledgers</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $totalLedgers }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-blue-600 dark:text-blue-400">Assets Total</p>
            <p class="text-base font-bold text-blue-700 dark:text-blue-300 mt-0.5">{{ $company->currency_symbol }}{{ number_format($assetTotal, 2) }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Liabilities</p>
            <p class="text-base font-bold text-amber-700 dark:text-amber-300 mt-0.5">{{ $company->currency_symbol }}{{ number_format($liabTotal, 2) }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-purple-600 dark:text-purple-400">Equity</p>
            <p class="text-base font-bold text-purple-700 dark:text-purple-300 mt-0.5">{{ $company->currency_symbol }}{{ number_format($equityTotal, 2) }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Income</p>
            <p class="text-base font-bold text-emerald-700 dark:text-emerald-300 mt-0.5">{{ $company->currency_symbol }}{{ number_format($incomeTotal, 2) }}</p>
        </div>
        <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-[11px] font-medium text-rose-600 dark:text-rose-400">Expenses</p>
            <p class="text-base font-bold text-rose-700 dark:text-rose-300 mt-0.5">{{ $company->currency_symbol }}{{ number_format($expenseTotal, 2) }}</p>
        </div>
    </div>

    <!-- Group Filter Tabs & Search -->
    <div class="glass-card p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Group Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold no-scrollbar">
            @php $currentGroup = request('group', 'all'); @endphp
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'all'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                All Accounts
            </a>
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'asset'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'asset' ? 'bg-blue-600 text-white shadow-xs' : 'text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40' }}">
                Assets
            </a>
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'liability'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'liability' ? 'bg-amber-600 text-white shadow-xs' : 'text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' }}">
                Liabilities
            </a>
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'equity'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'equity' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-700 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40' }}">
                Equity
            </a>
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'income'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'income' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' }}">
                Income
            </a>
            <a href="{{ route('ledgers.index', array_merge(request()->except('group'), ['group' => 'expense'])) }}"
                class="px-3 py-1.5 rounded-xl transition {{ $currentGroup === 'expense' ? 'bg-rose-600 text-white shadow-xs' : 'text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40' }}">
                Expenses
            </a>
        </div>

        <!-- Search input -->
        <form action="{{ route('ledgers.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="group" value="{{ $currentGroup }}">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ledger name or code..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600">
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700">
                Search
            </button>
        </form>
    </div>

    <!-- Ledgers Table -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Ledger Account</th>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Classification</th>
                        <th class="py-3.5 px-4 text-right">Opening Bal.</th>
                        <th class="py-3.5 px-4 text-right">Current Balance</th>
                        <th class="py-3.5 px-4 text-center">Entries</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @forelse($ledgers as $l)
                        @php
                            $badge = $l->group_badge;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('ledgers.show', $l->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-emerald-600 transition flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300">
                                        <i class="fa-solid fa-book-bookmark text-xs"></i>
                                    </div>
                                    <div>
                                        <span>{{ $l->name }}</span>
                                        @if($l->description)
                                            <p class="text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $l->description }}</p>
                                        @endif
                                    </div>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-500">
                                {{ $l->code ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] uppercase font-bold border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-slate-500">
                                {{ $company->currency_symbol }}{{ number_format($l->opening_balance, 2) }}
                                <span class="text-[9px] uppercase text-slate-400">({{ $l->opening_balance_type }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-white">
                                {{ $company->currency_symbol }}{{ number_format($l->current_balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[10px]">
                                    {{ $l->entries_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <!-- View Statement -->
                                <a href="{{ route('ledgers.show', $l->id) }}" 
                                    class="p-1.5 px-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition" 
                                    title="View Statement">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- One-Click Excel Export -->
                                <a href="{{ route('ledgers.export', $l->id) }}" 
                                    class="p-1.5 px-2.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition" 
                                    title="Export Statement to Excel">
                                    <i class="fa-solid fa-file-excel"></i>
                                </a>

                                @if(Auth::user()->canManageLedgers())
                                    <!-- Edit Ledger -->
                                    <button type="button" onclick="openEditLedgerModal({{ json_encode($l) }})" 
                                        class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 transition" 
                                        title="Edit Ledger">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Ledger -->
                                    <form action="{{ route('ledgers.destroy', $l->id) }}" method="POST" class="inline" 
                                        onsubmit="return confirm('Delete ledger \'{{ $l->name }}\' and all its transactions?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="p-1.5 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-600 transition" 
                                            title="Delete Ledger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-book-open text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm">No custom ledgers created yet.</p>
                                @if(Auth::user()->canManageLedgers())
                                    <button onclick="document.getElementById('addLedgerModal').classList.remove('hidden')" 
                                        class="mt-3 px-3.5 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-semibold">
                                        Create First Custom Ledger
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Ledger Modal -->
<div id="addLedgerModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-plus text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create Custom Ledger Account</h3>
            </div>
            <button onclick="document.getElementById('addLedgerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('ledgers.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ledger Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Office Rent, Director Loan, Audit Fee"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Code</label>
                    <input type="text" name="code" placeholder="e.g. 1001, LED-01"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono uppercase">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Classification (Group) *</label>
                <select name="group" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-medium">
                    <option value="expense">Expense (Direct &amp; Indirect Overheads)</option>
                    <option value="income">Income / Revenue (Sales, Fees &amp; Gains)</option>
                    <option value="asset">Asset (Fixed Assets, Equipment, Deposits)</option>
                    <option value="liability">Liability (Loans, Payables, Provisions)</option>
                    <option value="equity">Equity (Capital, Retained Earnings, Drawings)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Opening Balance</label>
                    <input type="number" name="opening_balance" step="0.01" min="0" value="0.00"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Opening Balance Type *</label>
                    <select name="opening_balance_type" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="debit">Debit (Dr)</option>
                        <option value="credit">Credit (Cr)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description / Notes</label>
                <textarea name="description" rows="2" placeholder="Brief note on account purpose..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addLedgerModal').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold shadow-sm">
                    Create Ledger
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Ledger Modal -->
<div id="editLedgerModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-pen-to-square text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Custom Ledger</h3>
            </div>
            <button onclick="document.getElementById('editLedgerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="editLedgerForm" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ledger Name *</label>
                    <input type="text" id="edit_ledger_name" name="name" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Code</label>
                    <input type="text" id="edit_ledger_code" name="code"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono uppercase">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Classification (Group) *</label>
                <select id="edit_ledger_group" name="group" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-medium">
                    <option value="expense">Expense (Direct &amp; Indirect Overheads)</option>
                    <option value="income">Income / Revenue (Sales, Fees &amp; Gains)</option>
                    <option value="asset">Asset (Fixed Assets, Equipment, Deposits)</option>
                    <option value="liability">Liability (Loans, Payables, Provisions)</option>
                    <option value="equity">Equity (Capital, Retained Earnings, Drawings)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Opening Balance</label>
                    <input type="number" id="edit_ledger_opening_balance" name="opening_balance" step="0.01" min="0"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Opening Balance Type *</label>
                    <select id="edit_ledger_opening_type" name="opening_balance_type" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                        <option value="debit">Debit (Dr)</option>
                        <option value="credit">Credit (Cr)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description / Notes</label>
                <textarea id="edit_ledger_description" name="description" rows="2"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_ledger_active" name="is_active" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                <label for="edit_ledger_active" class="font-medium text-slate-700 dark:text-slate-300">Account is Active</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('editLedgerModal').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditLedgerModal(l) {
        const form = document.getElementById('editLedgerForm');
        form.action = '{{ url("/ledgers") }}/' + l.id;

        document.getElementById('edit_ledger_name').value = l.name || '';
        document.getElementById('edit_ledger_code').value = l.code || '';
        document.getElementById('edit_ledger_group').value = (l.group || 'expense').toLowerCase();
        document.getElementById('edit_ledger_opening_balance').value = l.opening_balance || 0;
        document.getElementById('edit_ledger_opening_type').value = l.opening_balance_type || 'debit';
        document.getElementById('edit_ledger_description').value = l.description || '';
        document.getElementById('edit_ledger_active').checked = !!l.is_active;

        document.getElementById('editLedgerModal').classList.remove('hidden');
    }
</script>
@endsection
