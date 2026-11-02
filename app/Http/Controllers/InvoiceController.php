<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Tax;
use App\Models\Transaction;
use App\Mail\InvoiceSentMail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';
        $query = Invoice::with('customer')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->paginate(15);
        $customers = Customer::all();

        return view('invoices.index', compact('invoices', 'customers', 'company', 'currencySymbol'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items = Item::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $categories = Category::all();
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        // Auto generate next invoice number
        $lastId = Invoice::max('id') ?? 0;
        $nextNumber = 'INV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        $nextInvoiceNumber = $nextNumber;

        return view('invoices.create', compact('customers', 'items', 'taxes', 'categories', 'company', 'nextNumber', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $idx => $item) {
                if (!isset($item['name']) && isset($item['item_name'])) {
                    $items[$idx]['name'] = $item['item_name'];
                }
            }
            $request->merge(['items' => $items]);
        }
        if (!$request->has('discount_total') && $request->has('discount')) {
            $request->merge(['discount_total' => $request->input('discount')]);
        }

        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'discount_total' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxTotal = 0;
            $itemRows = [];

            foreach ($validated['items'] as $row) {
                $qty = (float) $row['quantity'];
                $price = (float) $row['price'];
                $taxRate = (float) ($row['tax_rate'] ?? 0);

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                $itemRows[] = [
                    'item_id' => $row['item_id'] ?? null,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $discount = (float) ($validated['discount_total'] ?? 0);
            $grandTotal = max(0, ($subtotal + $taxTotal) - $discount);

            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount_total' => $discount,
                'total' => $grandTotal,
                'paid_amount' => 0.00,
                'due_amount' => $grandTotal,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? 'Payment due within 30 days.',
                'public_token' => Str::random(40),
            ]);

            foreach ($itemRows as $itemData) {
                $invoice->items()->create($itemData);
            }

            // Update customer balance
            $customer = Customer::find($validated['customer_id']);
            if ($customer) {
                $customer->increment('balance', $grandTotal);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice created in PENDING status. Mark it as Sent whenever you wish to email the customer.');
    }

    public function show($id)
    {
        $company = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';
        $invoice = $id instanceof Invoice ? $id : Invoice::with(['customer', 'items', 'transactions.bankAccount'])->findOrFail($id);
        $bankAccounts = \Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'status')
            ? BankAccount::where('status', '!=', 'archived')->get()
            : BankAccount::all();

        return view('invoices.show', compact('invoice', 'company', 'currencySymbol', 'bankAccounts'));
    }

    public function print($id)
    {
        $company = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';
        $invoice = $id instanceof Invoice ? $id : Invoice::with(['customer', 'items'])->findOrFail($id);

        return view('invoices.print', compact('invoice', 'company', 'currencySymbol'));
    }

    public function publicShow($token)
    {
        return $this->publicView($token);
    }

    public function publicView($token)
    {
        $invoice = Invoice::where('public_token', $token)->with(['customer', 'items', 'transactions'])->firstOrFail();
        $company = Company::first() ?? new Company(['name' => 'WebotApp Enterprise', 'currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';

        return view('invoices.public', compact('invoice', 'company', 'currencySymbol'));
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->due_amount,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $amount = (float) $validated['amount'];

            // 1. Update Invoice totals
            $invoice->paid_amount += $amount;
            $invoice->due_amount = max(0, $invoice->total - $invoice->paid_amount);

            if ($invoice->due_amount <= 0.001) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();

            // 2. Update Customer Balance
            $customer = $invoice->customer;
            if ($customer) {
                $customer->decrement('balance', $amount);
            }

            // 3. Update Bank Account Balance
            $bankAccount = BankAccount::find($validated['bank_account_id']);
            if ($bankAccount) {
                $bankAccount->increment('current_balance', $amount);
            }

            // 4. Create Income Transaction in Ledger
            $incomeCatId = Category::where('type', 'income')->value('id');

            Transaction::create([
                'type' => 'income',
                'bank_account_id' => $validated['bank_account_id'],
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'category_id' => $incomeCatId,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? ('REC-' . strtoupper(Str::random(8))),
                'transaction_date' => $validated['payment_date'],
                'description' => $validated['description'] ?? ("Payment received for " . $invoice->invoice_number),
            ]);
        });

        return back()->with('success', 'Payment of ' . $validated['amount'] . ' recorded successfully.');
    }

    public function edit($id)
    {
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
        $currencySymbol = $company->currency_symbol ?? '₹';
        $invoice = $id instanceof Invoice ? $id : Invoice::with(['customer', 'items'])->findOrFail($id);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $items = Item::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        $categories = Category::all();

        return view('invoices.edit', compact('invoice', 'customers', 'items', 'taxes', 'categories', 'company', 'currencySymbol'));
    }

    public function update(Request $request, $id)
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::with('items')->findOrFail($id);

        $items = $request->input('items', []);
        if (is_array($items)) {
            foreach ($items as $idx => $item) {
                if (!isset($item['name']) && isset($item['item_name'])) {
                    $items[$idx]['name'] = $item['item_name'];
                }
            }
            $request->merge(['items' => $items]);
        }
        if (!$request->has('discount_total') && $request->has('discount')) {
            $request->merge(['discount_total' => $request->input('discount')]);
        }

        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'discount_total' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            // Reverse previous balance impact on customer
            $oldCustomer = Customer::find($invoice->customer_id);
            if ($oldCustomer) {
                $oldCustomer->decrement('balance', $invoice->due_amount);
            }

            $subtotal = 0;
            $taxTotal = 0;
            $itemRows = [];

            foreach ($validated['items'] as $row) {
                $qty = (float) $row['quantity'];
                $price = (float) $row['price'];
                $taxRate = (float) ($row['tax_rate'] ?? 0);

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;

                $itemRows[] = [
                    'item_id' => $row['item_id'] ?? null,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                ];
            }

            $discount = (float) ($validated['discount_total'] ?? 0);
            $grandTotal = max(0, ($subtotal + $taxTotal) - $discount);
            $paidAmount = (float) ($invoice->paid_amount ?? 0);
            $dueAmount = max(0, $grandTotal - $paidAmount);

            $status = $request->input('status', $invoice->status);
            if ($dueAmount <= 0.001 && $paidAmount > 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0 && $status === 'paid') {
                $status = 'partial';
            }

            $oldStatus = $invoice->status;

            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount_total' => $discount,
                'total' => $grandTotal,
                'due_amount' => $dueAmount,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? 'Payment due within 30 days.',
            ]);

            // Recreate line items
            $invoice->items()->delete();
            foreach ($itemRows as $itemData) {
                $invoice->items()->create($itemData);
            }

            // Apply new balance to customer
            $newCustomer = Customer::find($validated['customer_id']);
            if ($newCustomer) {
                $newCustomer->increment('balance', $dueAmount);
            }

            // If status changed to 'sent' from pending/draft, trigger automated email
            if ($status === 'sent' && $oldStatus !== 'sent') {
                $company = Company::first() ?? new Company(['currency_symbol' => '₹']);
                $this->sendInvoiceEmailNotification($invoice, $company);
            }
        });

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice updated successfully.');
    }

    /**
     * Quick status update action with automated email trigger when marked as Sent.
     */
    public function updateStatus(Request $request, $id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,sent,paid,partial,cancelled,draft',
        ]);

        $oldStatus = $invoice->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', "Invoice is already marked as " . strtoupper($newStatus) . ".");
        }

        $customer = $invoice->customer;
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        // Handle balance adjustments for cancellation
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            if ($customer) {
                $customer->decrement('balance', $invoice->due_amount);
            }
        } elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            if ($customer) {
                $customer->increment('balance', $invoice->due_amount);
            }
        }

        // Handle paid status directly
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $unpaid = $invoice->due_amount;
            $invoice->paid_amount = $invoice->total;
            $invoice->due_amount = 0.00;
            if ($customer && $unpaid > 0) {
                $customer->decrement('balance', $unpaid);
            }
        } elseif ($oldStatus === 'paid' && $newStatus !== 'paid') {
            // Revert from fully paid back to pending/sent
            $invoice->paid_amount = 0.00;
            $invoice->due_amount = $invoice->total;
            if ($customer) {
                $customer->increment('balance', $invoice->total);
            }
        }

        $invoice->status = $newStatus;
        $invoice->save();

        $emailNotice = '';
        if ($newStatus === 'sent') {
            $emailNotice = ' ' . $this->sendInvoiceEmailNotification($invoice, $company);
        }

        return back()->with('success', "Invoice #{$invoice->invoice_number} status updated to " . strtoupper($newStatus) . ".{$emailNotice}");
    }

    /**
     * Send or re-send automated invoice email directly to customer.
     */
    public function sendEmail(Request $request, $id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $company = Company::first() ?? new Company(['currency_symbol' => '₹']);

        $emailNotice = $this->sendInvoiceEmailNotification($invoice, $company);

        if ($invoice->status === 'pending' || $invoice->status === 'draft') {
            $invoice->status = 'sent';
            $invoice->save();
        }

        return back()->with('success', "Invoice marked as SENT. {$emailNotice}");
    }

    /**
     * Helper to safely send automated invoice email without breaking UI on SMTP failure.
     */
    protected function sendInvoiceEmailNotification(Invoice $invoice, ?Company $company = null): string
    {
        $customer = $invoice->customer;
        if (!$customer || empty($customer->email)) {
            return '(Note: Customer has no email address on file, email not sent.)';
        }

        try {
            Mail::to($customer->email)->send(new InvoiceSentMail($invoice, $company));
            return "(Automated email successfully sent to {$customer->email})";
        } catch (\Throwable $e) {
            Log::warning("Failed to deliver invoice email to {$customer->email}: " . $e->getMessage());
            return "(Mail notice: {$e->getMessage()})";
        }
    }

    public function destroy($id)
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        DB::transaction(function () use ($invoice) {
            // Revert customer balance
            $customer = Customer::find($invoice->customer_id);
            if ($customer) {
                $customer->decrement('balance', $invoice->due_amount);
            }

            // Revert any linked payments and transactions
            foreach ($invoice->transactions as $trx) {
                if ($trx->bankAccount) {
                    $trx->bankAccount->decrement('current_balance', $trx->amount);
                }
                $trx->delete();
            }

            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }
}
