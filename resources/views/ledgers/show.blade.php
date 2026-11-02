@extends('layouts.app')

@section('title', $ledger->name . ' - Ledger Statement')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('ledgers.index') }}" class="hover:text-emerald-500 transition">Custom Ledgers</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $ledger->name }}</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2.5">
                <span>{{ $ledger->name }}</span>
                @if($ledger->code)
                    <span class="text-xs font-mono font-medium px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        {{ $ledger->code }}
                    </span>
                @endif
                @php $badge = $ledger->group_badge; @endphp
                <span class="px-2.5 py-0.5 rounded-lg text-[10px] uppercase font-bold border {{ $badge['class'] }}">
                    {{ $badge['label'] }}
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Normal Balance: <strong>{{ $ledger->isDebitNormal() ? 'Debit (Dr)' : 'Credit (Cr)' }}</strong>
                @if($ledger->description) — {{ $ledger->description }} @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <!-- One-Click Excel Export (For Auditor & Filing) -->
            <a href="{{ route('ledgers.export', array_merge(['id' => $ledger->id], request()->only('start_date', 'end_date'))) }}" 
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="fa-solid fa-file-excel text-white"></i>
                <span class="text-white force-white">Export to Excel</span>
            </a>

            @if(Auth::user()->canManageLedgers())
                <button onclick="document.getElementById('addEntryModal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Post Journal Entry</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Statement Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-500">Period Opening Balance</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                {{ $company->currency_symbol }}{{ number_format($periodOpeningBal, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">As of {{ date('M d, Y', strtotime($startDate)) }}</p>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Total Debits (+)</p>
            <p class="text-lg font-bold text-blue-700 dark:text-blue-300 mt-1">
                {{ $company->currency_symbol }}{{ number_format($periodDebits, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ $entries->where('type', 'debit')->count() }} Debit entries</p>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Total Credits (-)</p>
            <p class="text-lg font-bold text-amber-700 dark:text-amber-300 mt-1">
                {{ $company->currency_symbol }}{{ number_format($periodCredits, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ $entries->where('type', 'credit')->count() }} Credit entries</p>
        </div>

        <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Period Closing Balance</p>
            <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                {{ $company->currency_symbol }}{{ number_format($periodClosingBal, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">As of {{ date('M d, Y', strtotime($endDate)) }}</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <form action="{{ route('ledgers.show', $ledger->id) }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="font-semibold text-slate-600 dark:text-slate-400">From:</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="font-semibold text-slate-600 dark:text-slate-400">To:</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white">
            </div>

            <button type="submit" 
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                Apply Date Filter
            </button>
        </form>
    </div>

    <!-- Ledger Statement Table -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Account Statement Entries</h3>
            <span class="text-[11px] text-slate-500">{{ $entries->count() }} transactions found</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Reference No.</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Type</th>
                        <th class="py-3 px-4 text-right">Debit (Dr)</th>
                        <th class="py-3 px-4 text-right">Credit (Cr)</th>
                        <th class="py-3 px-4 text-right">Running Balance</th>
                        @if(Auth::user()->canManageLedgers())
                            <th class="py-3 px-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    <!-- Opening Balance Row -->
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 font-medium">
                        <td class="py-3 px-4 text-slate-500">{{ date('Y-m-d', strtotime($startDate)) }}</td>
                        <td class="py-3 px-4 text-slate-400 font-mono">—</td>
                        <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                            <em>Opening Balance as of {{ date('M d, Y', strtotime($startDate)) }}</em>
                        </td>
                        <td class="py-3 px-4 text-center text-slate-400">—</td>
                        <td class="py-3 px-4 text-right text-slate-400">—</td>
                        <td class="py-3 px-4 text-right text-slate-400">—</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">
                            {{ $company->currency_symbol }}{{ number_format($periodOpeningBal, 2) }}
                        </td>
                        @if(Auth::user()->canManageLedgers())
                            <td class="py-3 px-4"></td>
                        @endif
                    </tr>

                    @php
                        $calcBal = $periodOpeningBal;
                    @endphp

                    @forelse($entries as $e)
                        @php
                            $amt = (float) $e->amount;
                            if ($ledger->isDebitNormal()) {
                                $calcBal += ($e->type === 'debit' ? $amt : -$amt);
                            } else {
                                $calcBal += ($e->type === 'credit' ? $amt : -$amt);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                {{ $e->entry_date->format('Y-m-d') }}
                            </td>
                            <td class="py-3 px-4 font-mono font-medium text-slate-500">
                                {{ $e->reference_number ?: '—' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                {{ $e->description }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($e->type === 'debit')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Debit (Dr)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Credit (Cr)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-blue-600 dark:text-blue-400">
                                {{ $e->type === 'debit' ? $company->currency_symbol . number_format($amt, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-amber-600 dark:text-amber-400">
                                {{ $e->type === 'credit' ? $company->currency_symbol . number_format($amt, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-white font-mono">
                                {{ $company->currency_symbol }}{{ number_format($calcBal, 2) }}
                            </td>
                            @if(Auth::user()->canManageLedgers())
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('ledgers.entries.destroy', ['id' => $ledger->id, 'entryId' => $e->id]) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this journal entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-red-50 text-slate-400 hover:text-red-600 transition" title="Delete Entry">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->canManageLedgers() ? 8 : 7 }}" class="py-8 text-center text-slate-400">
                                No entries posted in this date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-900/90 font-bold border-t border-slate-200 dark:border-slate-800 text-xs">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-right uppercase tracking-wider text-slate-600 dark:text-slate-400">Period Totals:</td>
                        <td class="py-3 px-4 text-right text-blue-700 dark:text-blue-300">{{ $company->currency_symbol }}{{ number_format($periodDebits, 2) }}</td>
                        <td class="py-3 px-4 text-right text-amber-700 dark:text-amber-300">{{ $company->currency_symbol }}{{ number_format($periodCredits, 2) }}</td>
                        <td class="py-3 px-4 text-right text-emerald-700 dark:text-emerald-300 font-black">{{ $company->currency_symbol }}{{ number_format($periodClosingBal, 2) }}</td>
                        @if(Auth::user()->canManageLedgers())
                            <td></td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Post Journal Entry Modal -->
@if(Auth::user()->canManageLedgers())
<div id="addEntryModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-receipt text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Post Journal Entry to {{ $ledger->name }}</h3>
            </div>
            <button onclick="document.getElementById('addEntryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('ledgers.entries.store', $ledger->id) }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Transaction Date *</label>
                    <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Entry Type *</label>
                    <select name="type" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-medium">
                        <option value="debit">Debit (Dr) - Increases Assets/Expenses, Decreases Liabilities/Income</option>
                        <option value="credit">Credit (Cr) - Increases Liabilities/Income, Decreases Assets/Expenses</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Amount ({{ $company->currency_symbol }}) *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-bold text-sm">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Reference / Voucher No.</label>
                    <input type="text" name="reference_number" placeholder="e.g. JV-102, CHQ-5524"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono uppercase">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Narration / Description *</label>
                <textarea name="description" rows="2.5" required placeholder="Detailed transaction explanation..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addEntryModal').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold shadow-sm">
                    Record Entry
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
