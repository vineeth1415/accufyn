@extends('layouts.app')

@section('title', 'Trial Balance Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('reports.profit_loss') }}" class="hover:text-emerald-500 transition">Financial Reports</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-300 font-semibold">Trial Balance</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2.5">
                <span>Trial Balance &amp; General Ledger Audit</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-semibold">Filing Report</span>
            </h1>
            <p class="text-xs text-slate-400">Comprehensive trial balance of all ledger accounts, banking balances, receivables, and payables</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- One-Click Excel Export (For Auditor & Tax Filing) -->
            <a href="{{ route('reports.trial_balance.export', ['as_of_date' => $asOfDate]) }}" 
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="fa-solid fa-file-excel text-white"></i>
                <span class="text-white force-white">Export to Excel</span>
            </a>
        </div>
    </div>

    <!-- Date Picker Filter -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between">
        <form action="{{ route('reports.trial_balance') }}" method="GET" class="flex items-center gap-3 text-xs">
            <label class="font-semibold text-slate-700 dark:text-slate-300">As of Date:</label>
            <input type="date" name="as_of_date" value="{{ $asOfDate }}" 
                class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-1.5 text-slate-900 dark:text-white">
            <button type="submit" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold rounded-xl border border-slate-300 dark:border-slate-700">
                Update Report
            </button>
        </form>

        <div class="text-xs text-slate-500">
            Reporting Entity: <strong>{{ $company->name ?? 'Enterprise' }}</strong>
        </div>
    </div>

    @php
        $sumDebit = 0;
        $sumCredit = 0;
    @endphp

    <!-- Trial Balance Sheet Table -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
        <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Account Balances Ledger</h3>
            <span class="text-[11px] text-slate-500">Currency: {{ $company->currency_code ?? 'INR' }} ({{ $company->currency_symbol }})</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Account Code</th>
                        <th class="py-3 px-4">Account Title / Particulars</th>
                        <th class="py-3 px-4">Classification</th>
                        <th class="py-3 px-4 text-right">Debit Balance (Dr)</th>
                        <th class="py-3 px-4 text-right">Credit Balance (Cr)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    <!-- Bank & Cash Accounts -->
                    @foreach($bankAccounts as $ba)
                        @php
                            $bal = (float) $ba->current_balance;
                            $deb = $bal >= 0 ? $bal : 0;
                            $crd = $bal < 0 ? abs($bal) : 0;
                            $sumDebit += $deb;
                            $sumCredit += $crd;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="py-2.5 px-4 font-mono font-medium text-slate-500">BANK-{{ $ba->id }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                {{ $ba->name }} <span class="font-normal text-slate-400 text-[11px]">({{ $ba->account_number ?: 'Cash In Hand' }})</span>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Bank &amp; Cash (Asset)
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-blue-700 dark:text-blue-300">
                                {{ $deb > 0 ? $company->currency_symbol . number_format($deb, 2) : '—' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-amber-700 dark:text-amber-300">
                                {{ $crd > 0 ? $company->currency_symbol . number_format($crd, 2) : '—' }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Accounts Receivable -->
                    @if($totalReceivables > 0)
                        @php $sumDebit += $totalReceivables; @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="py-2.5 px-4 font-mono font-medium text-slate-500">AR-001</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                Accounts Receivable <span class="font-normal text-slate-400 text-[11px]">(Debtors Outstanding)</span>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Current Asset
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-blue-700 dark:text-blue-300">
                                {{ $company->currency_symbol }}{{ number_format($totalReceivables, 2) }}
                            </td>
                            <td class="py-2.5 px-4 text-right text-slate-400">—</td>
                        </tr>
                    @endif

                    <!-- Accounts Payable -->
                    @if($totalPayables > 0)
                        @php $sumCredit += $totalPayables; @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="py-2.5 px-4 font-mono font-medium text-slate-500">AP-001</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                Accounts Payable <span class="font-normal text-slate-400 text-[11px]">(Creditors Outstanding)</span>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Current Liability
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right text-slate-400">—</td>
                            <td class="py-2.5 px-4 text-right font-bold text-amber-700 dark:text-amber-300">
                                {{ $company->currency_symbol }}{{ number_format($totalPayables, 2) }}
                            </td>
                        </tr>
                    @endif

                    <!-- Custom Ledgers -->
                    @foreach($ledgers as $l)
                        @php
                            $bal = (float) $l->current_balance;
                            $deb = 0;
                            $crd = 0;

                            if ($l->isDebitNormal()) {
                                if ($bal >= 0) $deb = $bal;
                                else $crd = abs($bal);
                            } else {
                                if ($bal >= 0) $crd = $bal;
                                else $deb = abs($bal);
                            }

                            $sumDebit += $deb;
                            $sumCredit += $crd;
                            $badge = $l->group_badge;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="py-2.5 px-4 font-mono font-medium text-slate-500">{{ $l->code ?: 'LED-' . $l->id }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('ledgers.show', $l->id) }}" class="hover:text-emerald-600 transition">
                                    {{ $l->name }}
                                </a>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] uppercase font-bold border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-blue-700 dark:text-blue-300">
                                {{ $deb > 0 ? $company->currency_symbol . number_format($deb, 2) : '—' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-amber-700 dark:text-amber-300">
                                {{ $crd > 0 ? $company->currency_symbol . number_format($crd, 2) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold border-t-2 border-slate-300 dark:border-slate-700 text-xs">
                    <tr>
                        <td colspan="3" class="py-3 px-4 text-right uppercase tracking-wider text-slate-700 dark:text-slate-200">
                            TRIAL BALANCE TOTALS:
                        </td>
                        <td class="py-3 px-4 text-right text-blue-700 dark:text-blue-300 text-sm font-black">
                            {{ $company->currency_symbol }}{{ number_format($sumDebit, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-amber-700 dark:text-amber-300 text-sm font-black">
                            {{ $company->currency_symbol }}{{ number_format($sumCredit, 2) }}
                        </td>
                    </tr>
                    @php
                        $diff = round($sumDebit - $sumCredit, 2);
                    @endphp
                    <tr>
                        <td colspan="3" class="py-2 px-4 text-right text-[11px] text-slate-500">Reconciliation Status:</td>
                        <td colspan="2" class="py-2 px-4 text-right font-semibold">
                            @if($diff === 0.0)
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1.5">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Balanced (Debit = Credit)</span>
                                </span>
                            @else
                                <span class="text-red-600 dark:text-red-400 flex items-center justify-end gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Difference: {{ $company->currency_symbol }}{{ number_format(abs($diff), 2) }}</span>
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
