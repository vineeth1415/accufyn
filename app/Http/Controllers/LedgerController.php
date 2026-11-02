<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = Ledger::withCount('entries');

        if ($request->filled('group') && $request->group !== 'all') {
            $query->where('group', strtolower($request->group));
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $ledgers = $query->orderBy('group', 'asc')->orderBy('name', 'asc')->get();

        // Financial KPIs
        $totalLedgers = Ledger::count();
        $assetTotal   = Ledger::where('group', 'asset')->sum('current_balance');
        $liabTotal    = Ledger::where('group', 'liability')->sum('current_balance');
        $equityTotal  = Ledger::where('group', 'equity')->sum('current_balance');
        $incomeTotal  = Ledger::where('group', 'income')->sum('current_balance');
        $expenseTotal = Ledger::where('group', 'expense')->sum('current_balance');

        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('ledgers.index', compact(
            'ledgers', 'totalLedgers', 'assetTotal', 'liabTotal', 'equityTotal', 'incomeTotal', 'expenseTotal', 'company'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:150',
            'code'                 => 'nullable|string|max:50|unique:ledgers,code',
            'group'                => 'required|string|in:asset,liability,equity,income,expense',
            'opening_balance'      => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|string|in:debit,credit',
            'description'          => 'nullable|string|max:500',
        ]);

        try {
            $openBal = (float) ($request->opening_balance ?? 0);
            $type    = $request->opening_balance_type;
            $group   = strtolower($request->group);

            // Normal balance sign check
            $isDebitNormal = in_array($group, ['asset', 'expense']);
            $currentBal    = $openBal;
            if (!$isDebitNormal && $type === 'debit') {
                $currentBal = -$openBal;
            } elseif ($isDebitNormal && $type === 'credit') {
                $currentBal = -$openBal;
            }

            $ledger = Ledger::create([
                'name'                 => trim($request->name),
                'code'                 => $request->filled('code') ? strtoupper(trim($request->code)) : null,
                'group'                => $group,
                'opening_balance'      => $openBal,
                'opening_balance_type' => $type,
                'current_balance'      => $currentBal,
                'description'          => $request->description ? trim($request->description) : null,
                'is_active'            => true,
                'created_by'           => Auth::id(),
            ]);

            return back()->with('success', "Custom Ledger '{$ledger->name}' ({$ledger->code}) created successfully.");
        } catch (\Throwable $e) {
            Log::error('Ledger creation failed: ' . $e->getMessage());
            return back()->with('error', 'Could not create ledger: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id, Request $request)
    {
        $ledger = Ledger::findOrFail($id);

        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        // Get entries within date range
        $entries = $ledger->entries()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate opening balance before start_date
        $priorDebits = $ledger->entries()
            ->where('entry_date', '<', $startDate)
            ->where('type', 'debit')
            ->sum('amount');

        $priorCredits = $ledger->entries()
            ->where('entry_date', '<', $startDate)
            ->where('type', 'credit')
            ->sum('amount');

        $initialBal = (float) $ledger->opening_balance;
        if (!$ledger->isDebitNormal() && $ledger->opening_balance_type === 'debit') {
            $initialBal = -$initialBal;
        } elseif ($ledger->isDebitNormal() && $ledger->opening_balance_type === 'credit') {
            $initialBal = -$initialBal;
        }

        $periodOpeningBal = $ledger->isDebitNormal()
            ? ($initialBal + $priorDebits - $priorCredits)
            : ($initialBal + $priorCredits - $priorDebits);

        // Period totals
        $periodDebits  = $entries->where('type', 'debit')->sum('amount');
        $periodCredits = $entries->where('type', 'credit')->sum('amount');

        $periodClosingBal = $ledger->isDebitNormal()
            ? ($periodOpeningBal + $periodDebits - $periodCredits)
            : ($periodOpeningBal + $periodCredits - $periodDebits);

        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('ledgers.show', compact(
            'ledger', 'entries', 'startDate', 'endDate',
            'periodOpeningBal', 'periodDebits', 'periodCredits', 'periodClosingBal', 'company'
        ));
    }

    public function storeEntry(Request $request, $id)
    {
        $ledger = Ledger::findOrFail($id);

        $request->validate([
            'entry_date'       => 'required|date',
            'type'             => 'required|string|in:debit,credit',
            'amount'           => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100',
            'description'      => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $ledger) {
                LedgerEntry::create([
                    'ledger_id'        => $ledger->id,
                    'entry_date'       => $request->entry_date,
                    'type'             => strtolower($request->type),
                    'amount'           => (float) $request->amount,
                    'reference_number' => $request->reference_number ? trim($request->reference_number) : null,
                    'description'      => trim($request->description),
                    'related_type'     => 'manual',
                    'created_by'       => Auth::id(),
                ]);

                $ledger->recalculateBalance();
            });

            return back()->with('success', 'Journal entry recorded successfully.');
        } catch (\Throwable $e) {
            Log::error('Ledger entry failed: ' . $e->getMessage());
            return back()->with('error', 'Could not record journal entry: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $ledger = Ledger::findOrFail($id);

        $request->validate([
            'name'                 => 'required|string|max:150',
            'code'                 => "nullable|string|max:50|unique:ledgers,code,{$id}",
            'group'                => 'required|string|in:asset,liability,equity,income,expense',
            'opening_balance'      => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|string|in:debit,credit',
            'description'          => 'nullable|string|max:500',
            'is_active'            => 'nullable|boolean',
        ]);

        try {
            $ledger->name                 = trim($request->name);
            $ledger->code                 = $request->filled('code') ? strtoupper(trim($request->code)) : null;
            $ledger->group                = strtolower($request->group);
            $ledger->opening_balance      = (float) ($request->opening_balance ?? 0);
            $ledger->opening_balance_type = $request->opening_balance_type;
            $ledger->description          = $request->description ? trim($request->description) : null;
            $ledger->is_active            = $request->boolean('is_active', true);
            $ledger->save();

            $ledger->recalculateBalance();

            return back()->with('success', "Ledger '{$ledger->name}' updated successfully.");
        } catch (\Throwable $e) {
            Log::error('Ledger update failed: ' . $e->getMessage());
            return back()->with('error', 'Could not update ledger: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $ledger = Ledger::findOrFail($id);
        $name = $ledger->name;
        $ledger->delete();

        return redirect()->route('ledgers.index')->with('success', "Ledger '{$name}' deleted successfully.");
    }

    public function destroyEntry($ledgerId, $entryId)
    {
        $ledger = Ledger::findOrFail($ledgerId);
        $entry  = LedgerEntry::where('ledger_id', $ledger->id)->findOrFail($entryId);
        $entry->delete();

        $ledger->recalculateBalance();

        return back()->with('success', 'Ledger entry deleted and balance recalculated.');
    }

    /**
     * Export Ledger Statement to Excel (CSV with UTF-8 BOM for full Excel compatibility).
     */
    public function export($id, Request $request)
    {
        $ledger = Ledger::findOrFail($id);

        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        $entries = $ledger->entries()
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $company = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => 'INR']);

        $filename = "Ledger_Statement_{$ledger->name}_{$startDate}_to_{$endDate}.csv";
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($ledger, $entries, $startDate, $endDate, $company) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens with proper encoding
            fputs($handle, "\xEF\xBB\xBF");

            // Company & Report Header
            fputcsv($handle, [$company->name ?? 'Company Ledger']);
            fputcsv($handle, ['Tax / GSTIN Number:', $company->tax_number ?? 'N/A']);
            fputcsv($handle, ['LEDGER ACCOUNT STATEMENT (AUDIT & TAX FILING REPORT)']);
            fputcsv($handle, ['Account Name:', $ledger->name, 'Account Code:', $ledger->code ?: 'N/A']);
            fputcsv($handle, ['Account Group:', strtoupper($ledger->group), 'Normal Balance:', $ledger->isDebitNormal() ? 'Debit' : 'Credit']);
            fputcsv($handle, ['Statement Period:', "{$startDate} to {$endDate}"]);
            fputcsv($handle, ['Exported At:', now()->format('Y-m-d H:i:s'), 'Exported By:', Auth::user()->name . ' (' . Auth::user()->role . ')']);
            fputcsv($handle, []); // Blank separator row

            // Opening Balance
            fputcsv($handle, ['Opening Balance:', number_format($ledger->opening_balance, 2), strtoupper($ledger->opening_balance_type)]);
            fputcsv($handle, []);

            // Table Header
            fputcsv($handle, ['#', 'Date', 'Reference No.', 'Description', 'Type', 'Debit Amount', 'Credit Amount', 'Running Balance']);

            $runningBal = (float) $ledger->opening_balance;
            if (!$ledger->isDebitNormal() && $ledger->opening_balance_type === 'debit') {
                $runningBal = -$runningBal;
            } elseif ($ledger->isDebitNormal() && $ledger->opening_balance_type === 'credit') {
                $runningBal = -$runningBal;
            }

            $totalDebits  = 0;
            $totalCredits = 0;
            $index = 1;

            foreach ($entries as $e) {
                $amt = (float) $e->amount;
                $debitCol  = $e->type === 'debit' ? number_format($amt, 2, '.', '') : '';
                $creditCol = $e->type === 'credit' ? number_format($amt, 2, '.', '') : '';

                if ($e->type === 'debit') {
                    $totalDebits += $amt;
                    $runningBal += ($ledger->isDebitNormal() ? $amt : -$amt);
                } else {
                    $totalCredits += $amt;
                    $runningBal += ($ledger->isDebitNormal() ? -$amt : $amt);
                }

                fputcsv($handle, [
                    $index++,
                    $e->entry_date->format('Y-m-d'),
                    $e->reference_number ?: '-',
                    $e->description,
                    strtoupper($e->type),
                    $debitCol,
                    $creditCol,
                    number_format($runningBal, 2, '.', ''),
                ]);
            }

            // Totals Row
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                'Total Transactions: ' . count($entries),
                '',
                number_format($totalDebits, 2, '.', ''),
                number_format($totalCredits, 2, '.', ''),
                number_format($runningBal, 2, '.', ''),
            ]);

            fputcsv($handle, ['CLOSING BALANCE', '', '', '', '', '', '', number_format($runningBal, 2, '.', '')]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
