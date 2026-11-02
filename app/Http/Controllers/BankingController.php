<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankingController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::withCount('transactions')->get();
        $totalBalance = $accounts->sum('current_balance');
        $recentTransactions = Transaction::with(['bankAccount', 'category'])->latest()->take(10)->get();
        $company = Company::first();

        return view('banking.index', compact('accounts', 'totalBalance', 'recentTransactions', 'company'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:100',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',
            'ifsc_code' => 'nullable|string|max:20',
            'branch_name' => 'nullable|string|max:100',
            'account_type' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $account = BankAccount::create([
            'name' => $request->account_name,
            'type' => $request->account_type ?: 'bank',
            'account_type' => $request->account_type ?: 'Current Account',
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => strtoupper($request->ifsc_code ?? ''),
            'branch_name' => $request->branch_name,
            'upi_id' => $request->upi_id,
            'currency' => $request->currency ?: 'INR',
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
            'bank_address' => $request->bank_address,
            'status' => 'active',
        ]);

        if ($request->opening_balance > 0) {
            Transaction::create([
                'bank_account_id' => $account->id,
                'type' => 'income',
                'amount' => $request->opening_balance,
                'transaction_date' => now()->toDateString(),
                'payment_method' => 'Opening Balance',
                'description' => 'Opening balance for ' . $account->name,
            ]);
        }

        return redirect()->route('banking.index')->with('success', 'Bank account registered successfully.');
    }

    public function transferForm()
    {
        $accounts = BankAccount::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
        return view('banking.transfer', compact('accounts'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
        ]);

        $fromAcc = BankAccount::findOrFail($request->from_account_id);
        $toAcc = BankAccount::findOrFail($request->to_account_id);

        if ($fromAcc->current_balance < $request->amount) {
            return back()->withInput()->with('error', 'Insufficient funds in ' . $fromAcc->name);
        }

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $fromAcc->decrement('current_balance', $amount);
            $toAcc->increment('current_balance', $amount);

            $ref = 'TRF-' . strtoupper(uniqid());

            // Debit from source
            Transaction::create([
                'bank_account_id' => $fromAcc->id,
                'type' => 'expense',
                'amount' => $amount,
                'transaction_date' => $request->transfer_date,
                'payment_method' => 'Transfer',
                'reference_number' => $ref,
                'description' => 'Internal transfer to ' . $toAcc->name,
            ]);

            // Credit to destination
            Transaction::create([
                'bank_account_id' => $toAcc->id,
                'type' => 'income',
                'amount' => $amount,
                'transaction_date' => $request->transfer_date,
                'payment_method' => 'Transfer',
                'reference_number' => $ref,
                'description' => 'Internal transfer from ' . $fromAcc->name,
            ]);

            DB::commit();
            return redirect()->route('banking.index')->with('success', 'Transfer of ₹' . number_format($amount, 2) . ' executed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function transactions(Request $request)
    {
        $query = Transaction::with(['bankAccount', 'category'])->latest();

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->paginate(25);
        $accounts = BankAccount::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('banking.transactions', compact('transactions', 'accounts', 'categories'));
    }

    public function destroyTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        DB::transaction(function () use ($transaction) {
            if ($transaction->bankAccount) {
                if ($transaction->type === 'income') {
                    $transaction->bankAccount->decrement('current_balance', $transaction->amount);
                } elseif ($transaction->type === 'expense') {
                    $transaction->bankAccount->increment('current_balance', $transaction->amount);
                }
            }
            $transaction->delete();
        });

        return back()->with('success', 'Transaction deleted and account balance updated successfully.');
    }

    public function updateAccount(Request $request, $id)
    {
        $account = BankAccount::findOrFail($id);

        $request->validate([
            'account_name' => 'required|string|max:100',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number,' . $id,
            'ifsc_code' => 'nullable|string|max:20',
            'branch_name' => 'nullable|string|max:100',
            'account_type' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'current_balance' => 'required|numeric',
        ]);

        $account->update([
            'name' => $request->account_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => strtoupper($request->ifsc_code ?? ''),
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type ?: 'Current Account',
            'upi_id' => $request->upi_id,
            'current_balance' => $request->current_balance,
        ]);

        return redirect()->route('banking.index')->with('success', 'Bank account updated successfully.');
    }

    public function destroyAccount($id)
    {
        $account = BankAccount::findOrFail($id);

        if (BankAccount::count() <= 1) {
            return back()->with('error', 'Cannot delete the only bank account.');
        }

        DB::transaction(function () use ($account) {
            $account->transactions()->delete();
            $account->delete();
        });

        return redirect()->route('banking.index')->with('success', 'Bank account and associated transactions deleted successfully.');
    }
}
