<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Company;
use App\Models\Item;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $query = Bill::with('vendor')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $bills = $query->paginate(15);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('bills.index', compact('bills', 'vendors', 'company'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $items = Item::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->get();
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $nextBillNumber = 'BILL-' . date('Y') . '-' . str_pad((Bill::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return view('bills.create', compact('vendors', 'categories', 'items', 'taxes', 'company', 'nextBillNumber'));
    }

    public function store(Request $request)
    {
        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $idx => $item) {
                if (!isset($item['item_name']) && isset($item['name'])) {
                    $items[$idx]['item_name'] = $item['name'];
                }
            }
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'required|unique:bills,bill_number',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($request->items as $it) {
                $lineSub = $it['quantity'] * $it['price'];
                $lineTax = 0;
                if (!empty($it['tax_rate'])) {
                    $lineTax = ($lineSub * $it['tax_rate']) / 100;
                }
                $subtotal += $lineSub;
                $taxTotal += $lineTax;
            }

            $discount = (float) ($request->discount ?? 0);
            $totalAmount = max(0, $subtotal + $taxTotal - $discount);

            $bill = Bill::create([
                'vendor_id' => $request->vendor_id,
                'bill_number' => $request->bill_number,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount_total' => $discount,
                'total' => $totalAmount,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'status' => 'received',
                'notes' => $request->notes ?? null,
            ]);

            foreach ($request->items as $it) {
                $lineSub = $it['quantity'] * $it['price'];
                $taxRate = !empty($it['tax_rate']) ? (float)$it['tax_rate'] : 0;
                $lineTax = ($lineSub * $taxRate) / 100;

                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_id' => $it['item_id'] ?? null,
                    'name' => $it['item_name'],
                    'quantity' => $it['quantity'],
                    'price' => $it['price'],
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineSub + $lineTax,
                ]);
            }

            // Increase vendor payable balance
            $vendor = Vendor::find($request->vendor_id);
            if ($vendor) {
                $vendor->increment('balance', $totalAmount);
            }

            DB::commit();
            return redirect()->route('bills.show', $bill->id)->with('success', 'Vendor bill created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating bill: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bill = Bill::with(['vendor', 'items'])->findOrFail($id);
        $bankAccounts = BankAccount::all();

        return view('bills.show', compact('bill', 'bankAccounts'));
    }

    public function print($id)
    {
        $bill = Bill::with(['vendor', 'items'])->findOrFail($id);
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';

        return view('bills.print', compact('bill', 'company', 'currencySymbol'));
    }

    public function edit($id)
    {
        $bill = Bill::with(['vendor', 'items'])->findOrFail($id);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('type', 'expense')->orderBy('name')->get();
        $items = Item::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        return view('bills.edit', compact('bill', 'vendors', 'categories', 'items', 'taxes', 'company'));
    }

    public function update(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'required|string|unique:bills,bill_number,' . $bill->id,
            'bill_date' => 'required|date',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            if ($bill->vendor) {
                $bill->vendor->decrement('balance', $bill->due_amount);
            }

            $subtotal = 0;
            $taxTotal = 0;
            foreach ($request->items as $it) {
                $lineSub = (float)$it['quantity'] * (float)$it['price'];
                $taxRate = !empty($it['tax_rate']) ? (float)$it['tax_rate'] : 0;
                $lineTax = ($lineSub * $taxRate) / 100;
                $subtotal += $lineSub;
                $taxTotal += $lineTax;
            }
            $totalAmount = $subtotal + $taxTotal;
            $paidAmount = (float)($bill->paid_amount ?? 0);
            $dueAmount = max(0, $totalAmount - $paidAmount);

            $status = $bill->status;
            if ($dueAmount <= 0.001 && $paidAmount > 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }

            $bill->update([
                'vendor_id' => $request->vendor_id,
                'category_id' => $request->category_id ?? $bill->category_id,
                'bill_number' => $request->bill_number,
                'order_number' => $request->order_number ?? null,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'amount' => $subtotal,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $totalAmount,
                'total_amount' => $totalAmount,
                'due_amount' => $dueAmount,
                'status' => $status,
                'notes' => $request->notes ?? null,
            ]);

            $bill->items()->delete();
            foreach ($request->items as $it) {
                $lineSub = (float)$it['quantity'] * (float)$it['price'];
                $taxRate = !empty($it['tax_rate']) ? (float)$it['tax_rate'] : 0;
                $lineTax = ($lineSub * $taxRate) / 100;

                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_id' => $it['item_id'] ?? null,
                    'name' => $it['item_name'],
                    'quantity' => $it['quantity'],
                    'price' => $it['price'],
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineSub + $lineTax,
                ]);
            }

            $vendor = Vendor::find($request->vendor_id);
            if ($vendor) {
                $vendor->increment('balance', $dueAmount);
            }

            DB::commit();
            return redirect()->route('bills.show', $bill->id)->with('success', 'Vendor bill updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating bill: ' . $e->getMessage());
        }
    }

    public function recordPayment(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $bill->due_amount,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $amount = (float) $request->amount;
            $bill->paid_amount += $amount;
            $bill->due_amount = max(0, $bill->total - $bill->paid_amount);

            if ($bill->due_amount <= 0) {
                $bill->status = 'paid';
            } else {
                $bill->status = 'partial';
            }
            $bill->save();

            // Deduct bank account balance
            $bank = BankAccount::findOrFail($request->bank_account_id);
            $bank->decrement('current_balance', $amount);

            // Record ledger transaction
            Transaction::create([
                'bank_account_id' => $bank->id,
                'vendor_id' => $bill->vendor_id,
                'bill_id' => $bill->id,
                'type' => 'expense',
                'amount' => $amount,
                'transaction_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number ?? ('PAY-' . $bill->bill_number),
                'description' => 'Payment for Bill ' . $bill->bill_number . ' to ' . ($bill->vendor->name ?? 'Vendor'),
            ]);

            // Deduct vendor payable balance
            if ($bill->vendor) {
                $bill->vendor->decrement('balance', $amount);
            }

            DB::commit();
            return back()->with('success', 'Payment recorded and bank account deducted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment recording failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $bill = Bill::findOrFail($id);

        DB::transaction(function () use ($bill) {
            if ($bill->vendor) {
                $bill->vendor->decrement('balance', $bill->due_amount);
            }
            foreach ($bill->transactions as $trx) {
                if ($trx->bankAccount) {
                    $trx->bankAccount->increment('current_balance', $trx->amount);
                }
                $trx->delete();
            }
            $bill->items()->delete();
            $bill->delete();
        });

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully.');
    }
}
