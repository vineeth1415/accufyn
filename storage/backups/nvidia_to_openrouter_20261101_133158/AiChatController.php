<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Tax;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $userMessage = trim($request->input('message', ''));
        $history = $request->input('history', []);

        if (empty($userMessage)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a message or voice prompt.',
            ], 400);
        }

        // Fetch live accounting system snapshot to give context to the LLM
        $company = Company::first() ?? new Company(['currency_symbol' => '₹', 'name' => 'WebotApp Enterprise']);
        $currencySymbol = $company->currency_symbol ?? '₹';

        $apiKey = !empty($company->nvidia_api_key) ? trim($company->nvidia_api_key) : trim(env('NVIDIA_API_KEY', ''));
        $model = !empty($company->nvidia_model) ? trim($company->nvidia_model) : env('NVIDIA_MODEL', 'meta/llama-3.2-11b-vision-instruct');

        if (empty($apiKey)) {
            $settingsUrl = route('settings.index');
            return response()->json([
                'success' => true,
                'reply' => "**NVIDIA API Key Required**\n\nNo default key is configured. Please enter your personal free NVIDIA API Key in Settings to activate the AI Copilot and Voice Engine.\n\n[Go to Settings to enter your NVIDIA API Key]({$settingsUrl})",
                'action' => 'none',
                'action_data' => null,
            ]);
        }

        $customers = Customer::where('is_active', true)->select('id', 'name', 'balance')->get();
        $vendors = Vendor::where('is_active', true)->select('id', 'name', 'balance')->get();
        $items = Item::where('is_active', true)->select('id', 'name', 'sku', 'sale_price', 'purchase_price')->get();
        $taxes = Tax::where('is_active', true)->select('id', 'name', 'rate')->get();
        $bankAccounts = BankAccount::where('status', '!=', 'archived')->select('id', 'name', 'account_number', 'current_balance')->get();

        $systemPrompt = <<<EOT
You are WebotApp AI, an intelligent, proactive executive accounting assistant built into the WebotApp Accounting platform.
Your job is to understand natural language and voice requests from the user, determine the appropriate accounting action, and output a structured JSON response.

CRITICAL FORMATTING RULES:
1. STRICTLY NO EMOJIS: Do not use any emojis under any circumstances in your responses. Keep all responses clean, professional, and purely text-based.
2. Voice-Friendly: Replies will be spoken aloud, so write clear and concise sentences without emoji icons.

CURRENT SYSTEM CONTEXT:
- Currency Symbol: {$currencySymbol}
- Active Customers: {$customers->toJson()}
- Active Vendors: {$vendors->toJson()}
- Active Products & Services: {$items->toJson()}
- Active Taxes: {$taxes->toJson()}
- Bank Accounts: {$bankAccounts->toJson()}
- Current Date: %CURRENT_DATE%

SUPPORTED ACTIONS:
1. create_invoice: Create a customer sales invoice / client bill.
   Use when the user asks to "bill someone", "create invoice for client", or "bill of rs 10000 for website designing services to mr rampal".
   Parameters:
     - party_name: string (e.g. "Mr. Rampal")
     - item_name: string (e.g. "Website Designing Services")
     - amount: number (e.g. 10000)
     - quantity: number (default 1)
     - tax_rate: number (optional, percentage e.g. 18 or 0)
     - notes: string (optional)

2. create_bill: Create a vendor purchase bill / expense bill.
   Use when the user specifically mentions a vendor purchase or expense bill (e.g. "create vendor bill from AWS for 5000", "bill from supplier").
   Parameters:
     - party_name: string (e.g. "AWS" or vendor name)
     - item_name: string (e.g. "Hosting Services")
     - amount: number
     - quantity: number (default 1)
     - tax_rate: number (optional)

3. add_customer: Add a new client / customer.
   Parameters:
     - name: string
     - email: string (optional)
     - phone: string (optional)

4. add_vendor: Add a new vendor / supplier.
   Parameters:
     - name: string
     - email: string (optional)
     - phone: string (optional)

5. check_party_pending: Check unpaid/pending balances (receivables from customers and payables to vendors).
   Parameters:
     - party_name: string (optional, or null for all parties)
     - party_type: "customer" | "vendor" | "all"

6. add_item: Add a product or service to catalog.
   Parameters:
     - name: string
     - sale_price: number
     - purchase_price: number (optional)
     - unit: string (optional, e.g. "pcs", "service", "month")

7. check_taxes: List available taxes.
   Parameters: {}

8. add_tax: Create a new tax rate.
   Parameters:
     - name: string (e.g. "GST 12%")
     - rate: number (e.g. 12)

9. check_banking: Check bank balances and transactions.
   Parameters: {}

10. check_reports: Check Profit & Loss or financial performance.
    Parameters:
      - report_type: "profit_loss" | "income_expense" | "tax_summary"

11. navigate: Direct the user to a specific sidebar section (invoices, bills, customers, vendors, items, banking, reports, settings).
    Parameters:
      - destination: string

12. none: General conversation, greeting, explanation, OR asking follow-up questions when required details are missing.

CRITICAL INSTRUCTIONS FOR MISSING DETAILS (VOICE & TEXT):
- If the user asks to create a bill or invoice, but did NOT specify:
  a) The party name (who the bill is for), OR
  b) The item/service description, OR
  c) The amount
  Then set "action" to "none", and in "message", politely ask the user to provide the missing details (e.g., "Who should I issue the bill/invoice to, what is the item or service description, and what is the amount?").
- If the user provides the party, item, and amount (like "create a bill of rs 10000 for website designing services to mr rampal"), immediately trigger the corresponding action!

OUTPUT FORMAT:
Always reply ONLY with a valid JSON object. Do not wrap in markdown or anything else if possible, or wrap in ```json ... ```:
{
  "message": "Human-friendly reply that will also be spoken by voice speech synthesis",
  "action": "action_name_or_none",
  "parameters": { ... }
}
EOT;

        $systemPrompt = str_replace('%CURRENT_DATE%', date('Y-m-d'), $systemPrompt);

        // Build messages array
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Include last 6 history messages for conversation context
        if (is_array($history)) {
            $recent = array_slice($history, -6);
            foreach ($recent as $msg) {
                if (isset($msg['role']) && isset($msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => (string) $msg['content'],
                    ];
                }
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Call NVIDIA NIM API
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://integrate.api.nvidia.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 600,
            ]);

            if (!$response->successful()) {
                Log::error('NVIDIA API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'AI Service error (' . $response->status() . '). ' . $response->body(),
                ], 502);
            }

            $jsonResp = $response->json();
            $rawContent = $jsonResp['choices'][0]['message']['content'] ?? '';

            // Clean up possible markdown code blocks
            $cleanJson = trim($rawContent);
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $cleanJson, $matches)) {
                $cleanJson = $matches[1];
            } elseif (preg_match('/(\{.*\})/s', $cleanJson, $matches)) {
                $cleanJson = $matches[1];
            }

            $parsed = json_decode($cleanJson, true);

            if (!is_array($parsed) || !isset($parsed['message'])) {
                // Fallback if parsing failed
                return response()->json([
                    'success' => true,
                    'reply' => $rawContent ?: 'I processed your request, but could not determine an action.',
                    'action' => 'none',
                    'action_data' => null,
                ]);
            }

            $action = $parsed['action'] ?? 'none';
            $params = $parsed['parameters'] ?? [];
            $replyMessage = $parsed['message'] ?? 'Done!';

            // Execute action if present
            $actionResult = $this->executeAction($action, $params, $currencySymbol);

            // If action produced additional text or links, merge nicely
            if (!empty($actionResult['reply_append'])) {
                $replyMessage .= "\n\n" . $actionResult['reply_append'];
            }

            // Strictly strip any emojis from the output
            $replyMessage = $this->stripEmojis($replyMessage);

            return response()->json([
                'success' => true,
                'reply' => $replyMessage,
                'action' => $action,
                'action_data' => $actionResult['data'] ?? null,
                'action_card' => $actionResult['card'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chatbot Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while contacting the AI assistant: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Autonomous Action Execution Engine
     */
    protected function executeAction(string $action, array $params, string $currencySymbol): array
    {
        $res = [
            'data' => null,
            'card' => null,
            'reply_append' => '',
        ];

        try {
            switch ($action) {
                case 'create_invoice':
                    $partyName = trim($params['party_name'] ?? '');
                    $itemName = trim($params['item_name'] ?? 'Consulting & Professional Services');
                    $amount = (float) ($params['amount'] ?? 0);
                    $qty = max(1, (float) ($params['quantity'] ?? 1));
                    $taxRate = (float) ($params['tax_rate'] ?? 0);

                    if (empty($partyName) || $amount <= 0) {
                        return [
                            'reply_append' => "*Please provide the client name and billing amount so I can generate the invoice.*"
                        ];
                    }

                    // Find or create customer
                    $customer = Customer::where('name', 'like', '%' . $partyName . '%')->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name' => $partyName,
                            'is_active' => true,
                            'currency' => 'INR',
                            'balance' => 0.00,
                        ]);
                    }

                    // Calculate totals
                    $subtotal = $qty * $amount;
                    $lineTax = ($subtotal * $taxRate) / 100;
                    $grandTotal = $subtotal + $lineTax;

                    // Next invoice number
                    $lastId = Invoice::max('id') ?? 0;
                    $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                    $invoice = Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'customer_id' => $customer->id,
                        'invoice_date' => date('Y-m-d'),
                        'due_date' => date('Y-m-d', strtotime('+30 days')),
                        'subtotal' => $subtotal,
                        'tax_total' => $lineTax,
                        'discount_total' => 0.00,
                        'total' => $grandTotal,
                        'paid_amount' => 0.00,
                        'due_amount' => $grandTotal,
                        'status' => 'sent',
                        'notes' => $params['notes'] ?? 'Generated via WebotApp AI Assistant',
                        'terms' => 'Payment due within 30 days.',
                        'public_token' => Str::random(40),
                    ]);

                    $invoice->items()->create([
                        'name' => $itemName,
                        'quantity' => $qty,
                        'price' => $amount,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'total' => $grandTotal,
                    ]);

                    $customer->increment('balance', $grandTotal);

                    $invoiceUrl = route('invoices.show', $invoice->id);
                    $printUrl = route('invoices.print', $invoice->id);

                    $res['data'] = [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoiceNumber,
                        'total' => $grandTotal,
                        'url' => $invoiceUrl,
                    ];

                    $res['card'] = [
                        'type' => 'invoice',
                        'title' => 'Invoice ' . $invoiceNumber . ' Created',
                        'party' => $customer->name,
                        'amount' => $currencySymbol . number_format($grandTotal, 2),
                        'item' => $itemName,
                        'status' => 'Issued / Due',
                        'url' => $invoiceUrl,
                        'print_url' => $printUrl,
                    ];

                    $res['reply_append'] = "**Invoice Link**: [View Invoice #{$invoiceNumber}]({$invoiceUrl}) | [Print / PDF]({$printUrl})";
                    break;

                case 'create_bill':
                    $partyName = trim($params['party_name'] ?? '');
                    $itemName = trim($params['item_name'] ?? 'Vendor Operating Expense');
                    $amount = (float) ($params['amount'] ?? 0);
                    $qty = max(1, (float) ($params['quantity'] ?? 1));
                    $taxRate = (float) ($params['tax_rate'] ?? 0);

                    if (empty($partyName) || $amount <= 0) {
                        return [
                            'reply_append' => "*Please provide the vendor name and bill amount so I can record the bill.*"
                        ];
                    }

                    // Find or create vendor
                    $vendor = Vendor::where('name', 'like', '%' . $partyName . '%')->first();
                    if (!$vendor) {
                        $vendor = Vendor::create([
                            'name' => $partyName,
                            'is_active' => true,
                            'currency' => 'INR',
                            'balance' => 0.00,
                        ]);
                    }

                    $subtotal = $qty * $amount;
                    $lineTax = ($subtotal * $taxRate) / 100;
                    $grandTotal = $subtotal + $lineTax;

                    $lastId = Bill::max('id') ?? 0;
                    $billNumber = 'BILL-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                    $bill = Bill::create([
                        'vendor_id' => $vendor->id,
                        'bill_number' => $billNumber,
                        'bill_date' => date('Y-m-d'),
                        'due_date' => date('Y-m-d', strtotime('+30 days')),
                        'subtotal' => $subtotal,
                        'tax_total' => $lineTax,
                        'discount_total' => 0.00,
                        'total' => $grandTotal,
                        'paid_amount' => 0.00,
                        'due_amount' => $grandTotal,
                        'status' => 'received',
                        'notes' => $params['notes'] ?? 'Generated via WebotApp AI Assistant',
                    ]);

                    BillItem::create([
                        'bill_id' => $bill->id,
                        'name' => $itemName,
                        'quantity' => $qty,
                        'price' => $amount,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'total' => $grandTotal,
                    ]);

                    $vendor->increment('balance', $grandTotal);

                    $billUrl = route('bills.show', $bill->id);

                    $res['data'] = [
                        'bill_id' => $bill->id,
                        'bill_number' => $billNumber,
                        'total' => $grandTotal,
                        'url' => $billUrl,
                    ];

                    $res['card'] = [
                        'type' => 'bill',
                        'title' => 'Vendor Bill ' . $billNumber . ' Created',
                        'party' => $vendor->name,
                        'amount' => $currencySymbol . number_format($grandTotal, 2),
                        'item' => $itemName,
                        'status' => 'Payable Pending',
                        'url' => $billUrl,
                    ];

                    $res['reply_append'] = "**Vendor Bill Link**: [View Bill #{$billNumber}]({$billUrl})";
                    break;

                case 'add_customer':
                    $name = trim($params['name'] ?? '');
                    if (empty($name)) {
                        return ['reply_append' => '*Please specify customer name.*'];
                    }
                    $c = Customer::create([
                        'name' => $name,
                        'email' => $params['email'] ?? null,
                        'phone' => $params['phone'] ?? null,
                        'currency' => 'INR',
                        'is_active' => true,
                        'balance' => 0.00,
                    ]);
                    $url = route('customers.show', $c->id);
                    $res['card'] = [
                        'type' => 'customer',
                        'title' => 'Customer Added: ' . $c->name,
                        'party' => $c->name,
                        'amount' => 'Active',
                        'url' => $url,
                    ];
                    $res['reply_append'] = "[View Customer Profile]({$url})";
                    break;

                case 'add_vendor':
                    $name = trim($params['name'] ?? '');
                    if (empty($name)) {
                        return ['reply_append' => '*Please specify vendor name.*'];
                    }
                    $v = Vendor::create([
                        'name' => $name,
                        'email' => $params['email'] ?? null,
                        'phone' => $params['phone'] ?? null,
                        'currency' => 'INR',
                        'is_active' => true,
                        'balance' => 0.00,
                    ]);
                    $url = route('vendors.show', $v->id);
                    $res['card'] = [
                        'type' => 'vendor',
                        'title' => 'Vendor Added: ' . $v->name,
                        'party' => $v->name,
                        'amount' => 'Active',
                        'url' => $url,
                    ];
                    $res['reply_append'] = "[View Vendor Profile]({$url})";
                    break;

                case 'check_party_pending':
                    $partyName = trim($params['party_name'] ?? '');
                    $partyType = $params['party_type'] ?? 'all';

                    $output = [];
                    $totalReceivables = Customer::sum('balance');
                    $totalPayables = Vendor::sum('balance');

                    if (!empty($partyName)) {
                        $cust = Customer::where('name', 'like', '%' . $partyName . '%')->first();
                        $vend = Vendor::where('name', 'like', '%' . $partyName . '%')->first();
                        if ($cust) {
                            $output[] = "**Customer**: [{$cust->name}](" . route('customers.show', $cust->id) . ") — Pending Receivable: **{$currencySymbol}" . number_format($cust->balance, 2) . "**";
                        }
                        if ($vend) {
                            $output[] = "**Vendor**: [{$vend->name}](" . route('vendors.show', $vend->id) . ") — Outstanding Payable: **{$currencySymbol}" . number_format($vend->balance, 2) . "**";
                        }
                        if (!$cust && !$vend) {
                            $output[] = "No party found matching \"{$partyName}\".";
                        }
                    } else {
                        $output[] = "**Total Customer Receivables**: **{$currencySymbol}" . number_format($totalReceivables, 2) . "**";
                        $output[] = "**Total Vendor Payables**: **{$currencySymbol}" . number_format($totalPayables, 2) . "**";

                        $dueCustomers = Customer::where('balance', '>', 0)->orderByDesc('balance')->take(5)->get();
                        if ($dueCustomers->count() > 0) {
                            $output[] = "\n*Top Pending Customers*:";
                            foreach ($dueCustomers as $dc) {
                                $output[] = "• [{$dc->name}](" . route('customers.show', $dc->id) . "): {$currencySymbol}" . number_format($dc->balance, 2);
                            }
                        }

                        $dueVendors = Vendor::where('balance', '>', 0)->orderByDesc('balance')->take(5)->get();
                        if ($dueVendors->count() > 0) {
                            $output[] = "\n*Top Pending Vendors*:";
                            foreach ($dueVendors as $dv) {
                                $output[] = "• [{$dv->name}](" . route('vendors.show', $dv->id) . "): {$currencySymbol}" . number_format($dv->balance, 2);
                            }
                        }
                    }

                    $res['reply_append'] = implode("\n", $output);
                    break;

                case 'add_item':
                    $name = trim($params['name'] ?? '');
                    $price = (float) ($params['sale_price'] ?? $params['price'] ?? 0);
                    $cost = (float) ($params['purchase_price'] ?? 0);
                    $unit = trim($params['unit'] ?? 'pcs');

                    if (empty($name)) {
                        return ['reply_append' => '*Please provide item name and price.*'];
                    }

                    $item = Item::create([
                        'name' => $name,
                        'sale_price' => $price,
                        'purchase_price' => $cost,
                        'unit' => $unit,
                        'is_active' => true,
                    ]);

                    $res['reply_append'] = "**Item Added**: {$item->name} (Price: {$currencySymbol}" . number_format($price, 2) . ") | [View Catalog](" . route('items.index') . ")";
                    break;

                case 'check_taxes':
                    $taxes = Tax::where('is_active', true)->get();
                    $taxList = [];
                    foreach ($taxes as $t) {
                        $taxList[] = "• **{$t->name}**: {$t->rate}%";
                    }
                    $res['reply_append'] = "**Configured Tax Rates**:\n" . implode("\n", $taxList) . "\n[Manage Taxes & Settings](" . route('settings.index') . ")";
                    break;

                case 'add_tax':
                    $name = trim($params['name'] ?? '');
                    $rate = (float) ($params['rate'] ?? 0);
                    if (empty($name)) {
                        return ['reply_append' => '*Please provide tax name and rate.*'];
                    }
                    $tax = Tax::create([
                        'name' => $name,
                        'rate' => $rate,
                        'is_active' => true,
                    ]);
                    $res['reply_append'] = "**Tax Created**: {$tax->name} ({$tax->rate}%) | [View Settings](" . route('settings.index') . ")";
                    break;

                case 'check_banking':
                    $accounts = BankAccount::where('status', '!=', 'archived')->get();
                    $totalCash = $accounts->sum('current_balance');
                    $accList = [];
                    foreach ($accounts as $a) {
                        $accList[] = "• **{$a->name}** (A/C: " . ($a->account_number ?: 'Cash') . "): **{$currencySymbol}" . number_format($a->current_balance, 2) . "**";
                    }
                    $res['reply_append'] = "**Bank & Cash Balances** (Total: **{$currencySymbol}" . number_format($totalCash, 2) . "**):\n" . implode("\n", $accList) . "\n[Open Banking Ledger](" . route('banking.index') . ") | [New Transfer](" . route('banking.transfer') . ")";
                    break;

                case 'check_reports':
                    $invoicedSales = (float) Invoice::where('status', '!=', 'cancelled')->sum('total');
                    $collectedSales = (float) Invoice::sum('paid_amount');
                    $billsExpense = (float) Bill::sum('total');
                    $netProfit = $invoicedSales - $billsExpense;

                    $res['reply_append'] = "**Profit & Loss Summary**:\n"
                        . "• **Gross Sales Invoiced**: {$currencySymbol}" . number_format($invoicedSales, 2) . "\n"
                        . "• **Cash Collected**: {$currencySymbol}" . number_format($collectedSales, 2) . "\n"
                        . "• **Total Operating Expenses & Bills**: {$currencySymbol}" . number_format($billsExpense, 2) . "\n"
                        . "• **Net Operating Profit**: **{$currencySymbol}" . number_format($netProfit, 2) . "**\n"
                        . "[Open Detailed Profit & Loss Report](" . route('reports.profit_loss') . ")";
                    break;

                case 'navigate':
                    $dest = strtolower($params['destination'] ?? '');
                    $routes = [
                        'invoices' => ['name' => 'Invoices & Sales', 'url' => route('invoices.index')],
                        'bills' => ['name' => 'Vendor Bills & Expenses', 'url' => route('bills.index')],
                        'customers' => ['name' => 'Customers', 'url' => route('customers.index')],
                        'vendors' => ['name' => 'Vendors & Suppliers', 'url' => route('vendors.index')],
                        'items' => ['name' => 'Products & Services Catalog', 'url' => route('items.index')],
                        'banking' => ['name' => 'Banking & Ledger', 'url' => route('banking.index')],
                        'transfer' => ['name' => 'Fund Transfer', 'url' => route('banking.transfer')],
                        'reports' => ['name' => 'Reports & Analytics', 'url' => route('reports.profit_loss')],
                        'settings' => ['name' => 'Company & Financial Settings', 'url' => route('settings.index')],
                    ];

                    foreach ($routes as $key => $info) {
                        if (str_contains($dest, $key)) {
                            $res['reply_append'] = "Direct link: [Go to {$info['name']}]({$info['url']})";
                            break;
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Action execution failed: ' . $e->getMessage());
            $res['reply_append'] = "*Action execution note: " . $e->getMessage() . "*";
        }

        return $res;
    }

    /**
     * Remove all emojis, pictographs, and symbols from string
     */
    protected function stripEmojis(string $text): string
    {
        return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{200D}\x{FE0F}]/u', '', $text);
    }
}
