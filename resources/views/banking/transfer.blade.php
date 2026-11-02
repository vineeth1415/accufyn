@extends('layouts.app')

@section('title', 'Transfer Funds')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Internal Account Transfer</h1>
            <p class="text-xs text-slate-400">Move funds between checking, savings, or physical cash drawers</p>
        </div>
        <a href="{{ route('banking.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Back to Banking</a>
    </div>

    <form action="{{ route('banking.transfer.post') }}" method="POST" class="glass-card p-6 rounded-2xl border border-slate-800 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Transfer From *</label>
            <select name="from_account_id" required class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2.5 text-white text-xs focus:outline-none focus:border-emerald-500">
                <option value="">Select Source Account</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->name }} (Available: {{ $currencySymbol }}{{ number_format($a->current_balance, 2) }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Transfer To *</label>
            <select name="to_account_id" required class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2.5 text-white text-xs focus:outline-none focus:border-emerald-500">
                <option value="">Select Destination Account</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->name }} (Current: {{ $currencySymbol }}{{ number_format($a->current_balance, 2) }})</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Amount ({{ $currencySymbol }}) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                    class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2.5 text-white text-xs font-bold focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Transfer Date *</label>
                <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required
                    class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2.5 text-white text-xs focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <div class="pt-3 border-t border-slate-800 flex justify-end gap-3">
            <a href="{{ route('banking.index') }}" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-emerald-950 transition">
                Execute Transfer
            </button>
        </div>
    </form>
</div>
@endsection
