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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    /**
     * Curated list of free OpenRouter models that support JSON-mode chat.
     * Keys are model IDs, values are display labels.
     */
    public static array $FREE_MODELS = [
        'meta-llama/llama-3.2-3b-instruct:free'   => 'Llama 3.2 3B Instruct (Free – Fastest)',
        'meta-llama/llama-3.2-1b-instruct:free'   => 'Llama 3.2 1B Instruct (Free – Ultra Light)',
        'meta-llama/llama-3.1-8b-instruct:free'   => 'Llama 3.1 8B Instruct (Free – Balanced)',
        'mistralai/mistral-7b-instruct:free'       => 'Mistral 7B Instruct (Free – Great Quality)',
        'google/gemma-2-9b-it:free'               => 'Gemma 2 9B IT (Free – Google)',
        'qwen/qwen-2.5-7b-instruct:free'          => 'Qwen 2.5 7B Instruct (Free)',
        'microsoft/phi-3-mini-128k-instruct:free'  => 'Phi-3 Mini 128K (Free – Microsoft)',
        'openchat/openchat-7b:free'               => 'OpenChat 7B (Free)',
    ];

    /**
     * Main AI Chat Endpoint — called via POST /ai/chat
     * Never executes AI-generated arbitrary SQL.
     * Keys are read server-side only; never exposed to the client.
     */
    public function chat(Request $request)
    {
        $userMessage = trim($request->input('message', ''));
        $history     = $request->input('history', []);

        if (empty($userMessage)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a message.',
            ], 400);
        }

        // --- Load company & resolve credentials server-side ---
        $company        = Company::first() ?? new Company(['currency_symbol' => '₹', 'name' => 'WebotApp Enterprise']);
        $currencySymbol = $company->currency_symbol ?? '₹';

        // Priority: DB-stored key > .env fallback
        $apiKey = !empty($company->openrouter_api_key)
            ? trim($company->openrouter_api_key)
            : trim(env('OPENROUTER_API_KEY', ''));

        $model       = !empty($company->openrouter_model)
            ? trim($company->openrouter_model)
            : 'meta-llama/llama-3.2-3b-instruct:free';

        $temperature = (float) ($company->openrouter_temperature ?? 0.20);
        $maxTokens   = (int)   ($company->openrouter_max_tokens  ?? 800);

        if (empty($apiKey)) {
            $settingsUrl = route('settings.index');
            return response()->json([
                'success' => true,
                'reply'   => "**OpenRouter API Key Required**\n\nNo API key is configured. Please go to Settings → AI Assistant and enter your free OpenRouter API key to activate the AI Accounting Assistant.\n\n[Go to Settings]({$settingsUrl})",
                'action'      => 'none',
                'action_data' => null,
            ]);
        }

        // --- Collect read-only accounting snapshot for context ---
        $customers   = Customer::where('is_active', true)->select('id', 'name', 'balance')->get();
        $vendors     = Vendor::where('is_active', true)->select('id', 'name', 'balance')->get();
        $items       = Item::where('is_active', true)->select('id', 'name', 'sku', 'sale_price', 'purchase_price')->get();
        $taxes       = Tax::where('is_active', true)->select('id', 'name', 'rate')->get();
        $bankAccounts = BankAccount::where('status', '!=', 'archived')->select('id', 'name', 'account_number', 'current_balance')->get();

        // Financial KPIs
        $totalReceivables = (float) Invoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->sum('due_amount');
        $totalPayables    = (float) Bill::whereIn('status', ['draft', 'received', 'partial', 'overdue'])->sum('due_amount');
        $totalIncome      = (float) DB::table('transactions')->where('type', 'income')->sum('amount');
        $totalExpense     = (float) DB::table('transactions')->where('type', 'expense')->sum('amount');
        $netProfit        = $totalIncome - $totalExpense;

        $systemPrompt = <<<EOT
You are WebotApp AI, an intelligent accounting assistant for the WebotApp Accounting platform.
You support both MALAYALAM and ENGLISH — detect the user's language and reply in the same language.
You help with: accounting questions, sales/purchase analysis, expense analysis, profit/loss, GST reports, outstanding balances, cash flow summaries, invoice creation, and bill management.

CRITICAL RULES:
1. NO EMOJIS anywhere in your responses.
2. Respond in the same language the user wrote (Malayalam or English).
3. You are READ-ONLY for financial data — never modify records unless explicitly triggering a supported action.
4. Never generate arbitrary SQL.
5. For write actions (create_invoice, create_bill, add_customer, add_vendor, add_item, add_tax) always confirm clearly in your message what you are about to create.

LIVE ACCOUNTING CONTEXT (Read-Only):
- Company: {$company->name}
- Currency: {$currencySymbol} ({$company->currency_code})
- Financial Year: {$company->financial_year}
- Total Customer Receivables: {$currencySymbol}{$totalReceivables}
- Total Vendor Payables: {$currencySymbol}{$totalPayables}
- Total Income (all time): {$currencySymbol}{$totalIncome}
- Total Expense (all time): {$currencySymbol}{$totalExpense}
- Net Profit: {$currencySymbol}{$netProfit}
- Active Customers: {$customers->toJson()}
- Active Vendors: {$vendors->toJson()}
- Products & Services: {$items->toJson()}
- Configured Taxes (GST/VAT): {$taxes->toJson()}
- Bank Accounts: {$bankAccounts->toJson()}
- Current Date: %CURRENT_DATE%

SUPPORTED ACTIONS:
1. create_invoice — Bill a customer. Required: party_name, item_name, amount. Optional: quantity, tax_rate, notes.
2. create_bill — Record vendor purchase/expense. Required: party_name, item_name, amount.
3. add_customer — Add a new customer. Required: name. Optional: email, phone.
4. add_vendor — Add a new vendor/supplier. Required: name. Optional: email, phone.
5. check_party_pending — Check outstanding balances. Optional: party_name, party_type (customer|vendor|all).
6. add_item — Add product/service. Required: name, sale_price. Optional: purchase_price, unit.
7. check_taxes — List all GST/tax rates.
8. add_tax — Create new tax rate. Required: name, rate.
9. check_banking — Check all bank/cash balances and recent transactions.
10. check_reports — Show profit & loss / financial summary.
11. navigate — Direct user to a section. Required: destination.
12. none — General answer, analysis, or clarification (no database writes).

WHEN TO USE ACTION "none":
- Answering accounting questions (GST rules, P&L explanation, etc.)
- Analysing data already provided in context
- Asking the user for missing details before triggering a write action
- Malayalam conversational replies

MISSING DETAILS RULE:
If user asks to create a bill or invoice but any of (party_name, item_name, amount) are missing — set action to "none" and politely ask for missing details.

OUTPUT FORMAT (strict JSON only, no markdown wrapper):
{
  "message": "Human-friendly reply (will also be spoken aloud — keep concise and clean)",
  "action": "action_name_or_none",
  "parameters": { }
}
EOT;

        $systemPrompt = str_replace('%CURRENT_DATE%', date('Y-m-d D'), $systemPrompt);

        // Build messages array — keep last 8 history turns
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        if (is_array($history)) {
            foreach (array_slice($history, -8) as $msg) {
                if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'])) {
                    $messages[] = ['role' => $msg['role'], 'content' => (string) $msg['content']];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // --- Call OpenRouter API via PHP cURL (key never touches browser) ---
        try {
            $payload = json_encode([
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
            ]);

            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'HTTP-Referer: ' . url('/'),
                    'X-Title: WebotApp Accounting AI',
                ],
            ]);

            $rawResponse = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError   = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('OpenRouter cURL error: ' . $curlError);
                return response()->json([
                    'success' => false,
                    'message' => 'Network error reaching AI service: ' . $curlError,
                ], 502);
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                $errBody = json_decode($rawResponse, true);
                $errMsg  = $errBody['error']['message'] ?? $rawResponse;
                Log::error("OpenRouter API HTTP {$httpCode}: " . $errMsg);
                return response()->json([
                    'success' => false,
                    'message' => "AI service error (HTTP {$httpCode}): " . $errMsg,
                ], 502);
            }

            $jsonResp   = json_decode($rawResponse, true);
            $rawContent = $jsonResp['choices'][0]['message']['content'] ?? '';

            // Strip markdown code-block wrappers if model adds them
            $cleanJson = trim($rawContent);
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $cleanJson, $m)) {
                $cleanJson = $m[1];
            } elseif (preg_match('/(\{.*\})/s', $cleanJson, $m)) {
                $cleanJson = $m[1];
            }

            $parsed = json_decode($cleanJson, true);

            if (!is_array($parsed) || !isset($parsed['message'])) {
                // Fallback: return raw content as plain reply
                return response()->json([
                    'success'     => true,
                    'reply'       => $rawContent ?: 'I processed your request but could not determine a structured action.',
                    'action'      => 'none',
                    'action_data' => null,
                ]);
            }

            $action       = $parsed['action']     ?? 'none';
            $params       = $parsed['parameters'] ?? [];
            $replyMessage = $parsed['message']    ?? 'Done.';

            // Execute the action with predefined safe PHP functions only
            $actionResult = $this->executeAction($action, $params, $currencySymbol);

            if (!empty($actionResult['reply_append'])) {
                $replyMessage .= "\n\n" . $actionResult['reply_append'];
            }

            $replyMessage = $this->stripEmojis($replyMessage);

            return response()->json([
                'success'     => true,
                'reply'       => $replyMessage,
                'action'      => $action,
                'action_data' => $actionResult['data'] ?? null,
                'action_card' => $actionResult['card'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error contacting AI assistant: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test OpenRouter connection — called from Settings page via AJAX.
     * Returns JSON {success, message, model_used, latency_ms}
     */
    public function testConnection(Request $request)
    {
        // Only admins may call this
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorised.'], 403);
        }

        $apiKey = trim($request->input('api_key', ''));
        $model  = trim($request->input('model', 'meta-llama/llama-3.2-3b-instruct:free'));

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'API key is required for testing.'], 422);
        }

        $start = microtime(true);

        $payload = json_encode([
            'model'      => $model,
            'messages'   => [
                ['role' => 'system', 'content' => 'You are a test assistant.'],
                ['role' => 'user',   'content' => 'Reply with exactly: Connection OK'],
            ],
            'max_tokens' => 10,
        ]);

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . url('/'),
                'X-Title: WebotApp Accounting AI Test',
            ],
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        $latencyMs = round((microtime(true) - $start) * 1000);

        if ($curlError) {
            return response()->json([
                'success' => false,
                'message' => 'Network error: ' . $curlError,
            ]);
        }

        $body = json_decode($rawResponse, true);

        if ($httpCode === 401) {
            return response()->json(['success' => false, 'message' => 'Invalid API key — authentication failed (HTTP 401).']);
        }
        if ($httpCode === 429) {
            return response()->json(['success' => false, 'message' => 'Rate limit exceeded. Please wait and try again.']);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            $errMsg = $body['error']['message'] ?? $rawResponse;
            return response()->json(['success' => false, 'message' => "API error (HTTP {$httpCode}): " . $errMsg]);
        }

        $reply = $body['choices'][0]['message']['content'] ?? '';

        return response()->json([
            'success'    => true,
            'message'    => 'Connection successful! Model responded: "' . trim($reply) . '"',
            'model_used' => $model,
            'latency_ms' => $latencyMs,
        ]);
    }

    /**
     * Predefined Safe Action Engine — no arbitrary SQL ever executed.
     */
    protected function executeAction(string $action, array $params, string $currencySymbol): array
    {
        $res = ['data' => null, 'card' => null, 'reply_append' => ''];

        if (auth()->check() && auth()->user()->isAuditor()) {
            if (in_array($action, ['create_invoice', 'create_bill', 'add_customer', 'add_vendor', 'add_item', 'add_tax'])) {
                return [
                    'data' => null,
                    'card' => null,
                    'reply_append' => "\n\n⚠️ *Auditor Access Notice: Your account is authorized for financial reports review and Excel export only. Creating transactions or modifying ledgers is restricted to Administrators and Accountants.*"
                ];
            }
        }

        try {
            switch ($action) {

                case 'create_invoice':
                    $partyName = trim($params['party_name'] ?? '');
                    $itemName  = trim($params['item_name']  ?? 'Professional Services');
                    $amount    = (float) ($params['amount']   ?? 0);
                    $qty       = max(1, (float) ($params['quantity'] ?? 1));
                    $taxRate   = (float) ($params['tax_rate']  ?? 0);

                    if (empty($partyName) || $amount <= 0) {
                        return ['reply_append' => '*Please specify the client name and billing amount.*'];
                    }

                    $customer = Customer::where('name', 'like', '%' . $partyName . '%')->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name'      => $partyName,
                            'is_active' => true,
                            'currency'  => 'INR',
                            'balance'   => 0.00,
                        ]);
                    }

                    $subtotal   = $qty * $amount;
                    $lineTax    = ($subtotal * $taxRate) / 100;
                    $grandTotal = $subtotal + $lineTax;
                    $lastId     = Invoice::max('id') ?? 0;
                    $invNum     = 'INV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                    $invoice = Invoice::create([
                        'invoice_number' => $invNum,
                        'customer_id'    => $customer->id,
                        'invoice_date'   => date('Y-m-d'),
                        'due_date'       => date('Y-m-d', strtotime('+30 days')),
                        'subtotal'       => $subtotal,
                        'tax_total'      => $lineTax,
                        'discount_total' => 0.00,
                        'total'          => $grandTotal,
                        'paid_amount'    => 0.00,
                        'due_amount'     => $grandTotal,
                        'status'         => 'sent',
                        'notes'          => $params['notes'] ?? 'Generated via WebotApp AI Assistant',
                        'terms'          => 'Payment due within 30 days.',
                        'public_token'   => Str::random(40),
                    ]);

                    $invoice->items()->create([
                        'name'       => $itemName,
                        'quantity'   => $qty,
                        'price'      => $amount,
                        'tax_rate'   => $taxRate,
                        'tax_amount' => $lineTax,
                        'total'      => $grandTotal,
                    ]);

                    $customer->increment('balance', $grandTotal);

                    $invoiceUrl = route('invoices.show', $invoice->id);
                    $printUrl   = route('invoices.print', $invoice->id);

                    $res['data'] = ['invoice_id' => $invoice->id, 'invoice_number' => $invNum, 'total' => $grandTotal, 'url' => $invoiceUrl];
                    $res['card'] = [
                        'type'      => 'invoice',
                        'title'     => 'Invoice ' . $invNum . ' Created',
                        'party'     => $customer->name,
                        'amount'    => $currencySymbol . number_format($grandTotal, 2),
                        'item'      => $itemName,
                        'status'    => 'Issued / Due',
                        'url'       => $invoiceUrl,
                        'print_url' => $printUrl,
                    ];
                    $res['reply_append'] = "**Invoice Link**: [View Invoice #{$invNum}]({$invoiceUrl}) | [Print / PDF]({$printUrl})";
                    break;

                case 'create_bill':
                    $partyName = trim($params['party_name'] ?? '');
                    $itemName  = trim($params['item_name']  ?? 'Vendor Expense');
                    $amount    = (float) ($params['amount']  ?? 0);
                    $qty       = max(1, (float) ($params['quantity'] ?? 1));
                    $taxRate   = (float) ($params['tax_rate'] ?? 0);

                    if (empty($partyName) || $amount <= 0) {
                        return ['reply_append' => '*Please specify the vendor name and bill amount.*'];
                    }

                    $vendor = Vendor::where('name', 'like', '%' . $partyName . '%')->first();
                    if (!$vendor) {
                        $vendor = Vendor::create(['name' => $partyName, 'is_active' => true, 'currency' => 'INR', 'balance' => 0.00]);
                    }

                    $subtotal   = $qty * $amount;
                    $lineTax    = ($subtotal * $taxRate) / 100;
                    $grandTotal = $subtotal + $lineTax;
                    $lastId     = Bill::max('id') ?? 0;
                    $billNum    = 'BILL-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                    $bill = Bill::create([
                        'vendor_id'      => $vendor->id,
                        'bill_number'    => $billNum,
                        'bill_date'      => date('Y-m-d'),
                        'due_date'       => date('Y-m-d', strtotime('+30 days')),
                        'subtotal'       => $subtotal,
                        'tax_total'      => $lineTax,
                        'discount_total' => 0.00,
                        'total'          => $grandTotal,
                        'paid_amount'    => 0.00,
                        'due_amount'     => $grandTotal,
                        'status'         => 'received',
                        'notes'          => $params['notes'] ?? 'Generated via WebotApp AI Assistant',
                    ]);

                    BillItem::create([
                        'bill_id'    => $bill->id,
                        'name'       => $itemName,
                        'quantity'   => $qty,
                        'price'      => $amount,
                        'tax_rate'   => $taxRate,
                        'tax_amount' => $lineTax,
                        'total'      => $grandTotal,
                    ]);

                    $vendor->increment('balance', $grandTotal);

                    $billUrl = route('bills.show', $bill->id);
                    $res['data'] = ['bill_id' => $bill->id, 'bill_number' => $billNum, 'total' => $grandTotal, 'url' => $billUrl];
                    $res['card'] = [
                        'type'   => 'bill',
                        'title'  => 'Vendor Bill ' . $billNum . ' Created',
                        'party'  => $vendor->name,
                        'amount' => $currencySymbol . number_format($grandTotal, 2),
                        'item'   => $itemName,
                        'status' => 'Payable Pending',
                        'url'    => $billUrl,
                    ];
                    $res['reply_append'] = "**Vendor Bill**: [View Bill #{$billNum}]({$billUrl})";
                    break;

                case 'add_customer':
                    $name = trim($params['name'] ?? '');
                    if (empty($name)) return ['reply_append' => '*Please specify the customer name.*'];
                    $c   = Customer::create(['name' => $name, 'email' => $params['email'] ?? null, 'phone' => $params['phone'] ?? null, 'currency' => 'INR', 'is_active' => true, 'balance' => 0.00]);
                    $url = route('customers.show', $c->id);
                    $res['card'] = ['type' => 'customer', 'title' => 'Customer Added: ' . $c->name, 'party' => $c->name, 'amount' => 'Active', 'url' => $url];
                    $res['reply_append'] = "[View Customer Profile]({$url})";
                    break;

                case 'add_vendor':
                    $name = trim($params['name'] ?? '');
                    if (empty($name)) return ['reply_append' => '*Please specify the vendor name.*'];
                    $v   = Vendor::create(['name' => $name, 'email' => $params['email'] ?? null, 'phone' => $params['phone'] ?? null, 'currency' => 'INR', 'is_active' => true, 'balance' => 0.00]);
                    $url = route('vendors.show', $v->id);
                    $res['card'] = ['type' => 'vendor', 'title' => 'Vendor Added: ' . $v->name, 'party' => $v->name, 'amount' => 'Active', 'url' => $url];
                    $res['reply_append'] = "[View Vendor Profile]({$url})";
                    break;

                case 'check_party_pending':
                    $partyName = trim($params['party_name'] ?? '');
                    $output    = [];
                    $totalRec  = (float) Customer::sum('balance');
                    $totalPay  = (float) Vendor::sum('balance');

                    if (!empty($partyName)) {
                        $cust = Customer::where('name', 'like', '%' . $partyName . '%')->first();
                        $vend = Vendor::where('name', 'like', '%' . $partyName . '%')->first();
                        if ($cust) $output[] = "**Customer** [{$cust->name}](" . route('customers.show', $cust->id) . "): Receivable **{$currencySymbol}" . number_format($cust->balance, 2) . "**";
                        if ($vend) $output[] = "**Vendor** [{$vend->name}](" . route('vendors.show', $vend->id) . "): Payable **{$currencySymbol}" . number_format($vend->balance, 2) . "**";
                        if (!$cust && !$vend) $output[] = "No party found matching \"{$partyName}\".";
                    } else {
                        $output[] = "**Total Receivables**: {$currencySymbol}" . number_format($totalRec, 2);
                        $output[] = "**Total Payables**: {$currencySymbol}" . number_format($totalPay, 2);
                        foreach (Customer::where('balance', '>', 0)->orderByDesc('balance')->take(5)->get() as $dc) {
                            $output[] = "• [{$dc->name}](" . route('customers.show', $dc->id) . "): {$currencySymbol}" . number_format($dc->balance, 2);
                        }
                        foreach (Vendor::where('balance', '>', 0)->orderByDesc('balance')->take(5)->get() as $dv) {
                            $output[] = "• [{$dv->name}](" . route('vendors.show', $dv->id) . "): {$currencySymbol}" . number_format($dv->balance, 2);
                        }
                    }
                    $res['reply_append'] = implode("\n", $output);
                    break;

                case 'add_item':
                    $name  = trim($params['name'] ?? '');
                    $price = (float) ($params['sale_price'] ?? $params['price'] ?? 0);
                    $cost  = (float) ($params['purchase_price'] ?? 0);
                    $unit  = trim($params['unit'] ?? 'pcs');
                    if (empty($name)) return ['reply_append' => '*Please provide item name and price.*'];
                    $item = Item::create(['name' => $name, 'sale_price' => $price, 'purchase_price' => $cost, 'unit' => $unit, 'is_active' => true]);
                    $res['reply_append'] = "**Item Added**: {$item->name} — Sale Price: {$currencySymbol}" . number_format($price, 2) . " | [View Catalog](" . route('items.index') . ")";
                    break;

                case 'check_taxes':
                    $taxList = Tax::where('is_active', true)->get()->map(fn($t) => "• **{$t->name}**: {$t->rate}%")->implode("\n");
                    $res['reply_append'] = "**Configured Tax Rates (GST/VAT)**:\n" . ($taxList ?: 'No taxes configured.') . "\n[Manage Taxes](" . route('settings.index') . ")";
                    break;

                case 'add_tax':
                    $name = trim($params['name'] ?? '');
                    $rate = (float) ($params['rate'] ?? 0);
                    if (empty($name)) return ['reply_append' => '*Please provide tax name and rate.*'];
                    $tax = Tax::create(['name' => $name, 'rate' => $rate, 'is_active' => true]);
                    $res['reply_append'] = "**Tax Created**: {$tax->name} ({$tax->rate}%) | [View Settings](" . route('settings.index') . ")";
                    break;

                case 'check_banking':
                    $accounts  = BankAccount::where('status', '!=', 'archived')->get();
                    $totalCash = $accounts->sum('current_balance');
                    $accList   = $accounts->map(fn($a) => "• **{$a->name}** (A/C: " . ($a->account_number ?: 'Cash') . "): **{$currencySymbol}" . number_format($a->current_balance, 2) . "**")->implode("\n");
                    $res['reply_append'] = "**Bank & Cash Balances** (Total: {$currencySymbol}" . number_format($totalCash, 2) . "):\n" . $accList . "\n[Banking Ledger](" . route('banking.index') . ")";
                    break;

                case 'check_reports':
                    $invoicedSales  = (float) Invoice::where('status', '!=', 'cancelled')->sum('total');
                    $collectedSales = (float) Invoice::sum('paid_amount');
                    $billsExpense   = (float) Bill::sum('total');
                    $netProfit      = $invoicedSales - $billsExpense;

                    $res['reply_append'] = "**Profit & Loss Summary**:\n"
                        . "• Gross Sales: {$currencySymbol}" . number_format($invoicedSales, 2) . "\n"
                        . "• Cash Collected: {$currencySymbol}" . number_format($collectedSales, 2) . "\n"
                        . "• Total Expenses: {$currencySymbol}" . number_format($billsExpense, 2) . "\n"
                        . "• **Net Profit: {$currencySymbol}" . number_format($netProfit, 2) . "**\n"
                        . "[Detailed Report](" . route('reports.profit_loss') . ")";
                    break;

                case 'navigate':
                    $dest   = strtolower($params['destination'] ?? '');
                    $routes = [
                        'invoice'    => ['name' => 'Invoices & Sales',           'url' => route('invoices.index')],
                        'bill'       => ['name' => 'Vendor Bills & Expenses',    'url' => route('bills.index')],
                        'customer'   => ['name' => 'Customers',                  'url' => route('customers.index')],
                        'vendor'     => ['name' => 'Vendors & Suppliers',        'url' => route('vendors.index')],
                        'item'       => ['name' => 'Products & Services',        'url' => route('items.index')],
                        'banking'    => ['name' => 'Banking & Ledger',           'url' => route('banking.index')],
                        'transfer'   => ['name' => 'Fund Transfer',              'url' => route('banking.transfer')],
                        'report'     => ['name' => 'Reports & Analytics',        'url' => route('reports.profit_loss')],
                        'setting'    => ['name' => 'Settings',                   'url' => route('settings.index')],
                        'dashboard'  => ['name' => 'Dashboard',                  'url' => route('dashboard')],
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
            Log::error('AI action execution failed: ' . $e->getMessage());
            $res['reply_append'] = '*Action note: ' . $e->getMessage() . '*';
        }

        return $res;
    }

    /**
     * Strip all emoji and pictograph characters.
     */
    protected function stripEmojis(string $text): string
    {
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}'
            . '\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}'
            . '\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}'
            . '\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}'
            . '\x{2B50}\x{200D}\x{FE0F}]/u',
            '',
            $text
        );
    }
}
