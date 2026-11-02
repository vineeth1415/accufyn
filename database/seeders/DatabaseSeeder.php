<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ensure Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@webotapp.com'],
            [
                'name' => 'WebotApp Administrator',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
                'is_active' => true,
            ]
        );

        // 2. Company Profile
        DB::table('companies')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'WebotApp Technologies Pvt Ltd',
                'email' => 'accounting@webotapp.com',
                'phone' => '+91 7002484119',
                'address' => 'Tech Innovation Hub, MG Road',
                'city' => 'Guwahati',
                'country' => 'India',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'tax_number' => 'GSTIN18AABCT2345K1Z5',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Default Categories
        $categories = [
            ['name' => 'Product Sales', 'type' => 'income', 'color' => '#10b981'],
            ['name' => 'Software Consulting', 'type' => 'income', 'color' => '#06b6d4'],
            ['name' => 'Maintenance & Retainers', 'type' => 'income', 'color' => '#3b82f6'],
            ['name' => 'Office Rent & Space', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Salaries & Wages', 'type' => 'expense', 'color' => '#f59e0b'],
            ['name' => 'Server & Cloud Hosting', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Utilities & Internet', 'type' => 'expense', 'color' => '#ec4899'],
            ['name' => 'Marketing & Advertising', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Office Supplies', 'type' => 'expense', 'color' => '#64748b'],
        ];
        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['name' => $cat['name']], array_merge($cat, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 4. Default Taxes
        $taxes = [
            ['name' => 'Standard Rate (18%)', 'rate' => 18.00, 'type' => 'normal'],
            ['name' => 'Reduced Rate (5%)', 'rate' => 5.00, 'type' => 'normal'],
            ['name' => 'Exempt / Zero Tax (0%)', 'rate' => 0.00, 'type' => 'normal'],
        ];
        foreach ($taxes as $tax) {
            DB::table('taxes')->updateOrInsert(['name' => $tax['name']], array_merge($tax, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 5. Default Bank & Cash Accounts
        $bankAccount1 = DB::table('bank_accounts')->updateOrInsert(
            ['name' => 'Primary Operating Account (HDFC)'],
            [
                'type' => 'bank',
                'account_number' => '50200034981290',
                'bank_name' => 'HDFC Bank Ltd',
                'currency' => 'USD',
                'opening_balance' => 25000.00,
                'current_balance' => 38450.00,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $bankAccount2 = DB::table('bank_accounts')->updateOrInsert(
            ['name' => 'Corporate Expense Card'],
            [
                'type' => 'card',
                'account_number' => '4129-XXXX-XXXX-8812',
                'bank_name' => 'Chase Commercial',
                'currency' => 'USD',
                'opening_balance' => 5000.00,
                'current_balance' => 3200.00,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $bankAccount3 = DB::table('bank_accounts')->updateOrInsert(
            ['name' => 'Petty Cash Office Drawer'],
            [
                'type' => 'cash',
                'account_number' => 'CASH-001',
                'bank_name' => 'Internal Vault',
                'currency' => 'USD',
                'opening_balance' => 1500.00,
                'current_balance' => 1120.00,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 6. Default Items (Products & Services)
        $tax18Id = DB::table('taxes')->where('rate', 18.00)->value('id');
        $catSalesId = DB::table('categories')->where('name', 'Product Sales')->value('id');
        $catConsultId = DB::table('categories')->where('name', 'Software Consulting')->value('id');

        $items = [
            [
                'name' => 'Enterprise Cloud ERP License',
                'sku' => 'SW-ERP-ENT',
                'description' => 'Annual enterprise license for core business automation',
                'category_id' => $catSalesId,
                'sale_price' => 2400.00,
                'purchase_price' => 800.00,
                'tax_id' => $tax18Id,
                'unit' => 'license',
            ],
            [
                'name' => 'Senior Architecture Consulting (Hourly)',
                'sku' => 'SRV-ARCH-HR',
                'description' => 'Dedicated engineering and accounting system architecture',
                'category_id' => $catConsultId,
                'sale_price' => 120.00,
                'purchase_price' => 0.00,
                'tax_id' => $tax18Id,
                'unit' => 'hour',
            ],
            [
                'name' => 'Database Migration & Security Audit',
                'sku' => 'SRV-AUDIT-DB',
                'description' => 'Comprehensive database health, indexing, and compliance inspection',
                'category_id' => $catConsultId,
                'sale_price' => 1500.00,
                'purchase_price' => 300.00,
                'tax_id' => $tax18Id,
                'unit' => 'project',
            ],
        ];
        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(['sku' => $item['sku']], array_merge($item, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 7. Default Customers
        $customers = [
            [
                'name' => 'Apex Global Logistics Inc.',
                'email' => 'billing@apexlogistics.com',
                'phone' => '+1 (555) 234-8901',
                'company_name' => 'Apex Global',
                'tax_number' => 'US-EIN-984210',
                'address' => '742 Evergreen Terrace',
                'city' => 'New York',
                'country' => 'United States',
                'balance' => 3800.00,
            ],
            [
                'name' => 'Nexus Retail Solutions',
                'email' => 'accounts@nexusretail.co.uk',
                'phone' => '+44 20 7946 0912',
                'company_name' => 'Nexus Retail Ltd',
                'tax_number' => 'GB-VAT-891048',
                'address' => '12 High St, Kensington',
                'city' => 'London',
                'country' => 'United Kingdom',
                'balance' => 1200.00,
            ],
            [
                'name' => 'Pinnacle Health Diagnostics',
                'email' => 'finance@pinnaclehealth.in',
                'phone' => '+91 98450 11223',
                'company_name' => 'Pinnacle Healthcare',
                'tax_number' => 'GSTIN27AABCP1122D1Z8',
                'address' => 'Plot 45, Bandra Kurla Complex',
                'city' => 'Mumbai',
                'country' => 'India',
                'balance' => 0.00,
            ],
        ];
        foreach ($customers as $cust) {
            DB::table('customers')->updateOrInsert(['email' => $cust['email']], array_merge($cust, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 8. Default Vendors
        $vendors = [
            [
                'name' => 'Amazon Web Services (AWS)',
                'email' => 'invoicing@aws.amazon.com',
                'phone' => '+1 (800) 555-0199',
                'company_name' => 'Amazon Web Services Inc.',
                'tax_number' => 'US-EIN-412390',
                'address' => '410 Terry Ave N',
                'city' => 'Seattle',
                'country' => 'United States',
                'balance' => 840.00,
            ],
            [
                'name' => 'Regus Commercial Real Estate',
                'email' => 'accounts@regus.com',
                'phone' => '+91 22 6100 4455',
                'company_name' => 'IWG / Regus India',
                'tax_number' => 'GSTIN29AAACR4455E1Z2',
                'address' => 'Tower B, World Trade Center',
                'city' => 'Bangalore',
                'country' => 'India',
                'balance' => 1500.00,
            ],
        ];
        foreach ($vendors as $v) {
            DB::table('vendors')->updateOrInsert(['email' => $v['email']], array_merge($v, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 9. Sample Invoices & Invoice Items
        $custId1 = DB::table('customers')->where('email', 'billing@apexlogistics.com')->value('id');
        $custId2 = DB::table('customers')->where('email', 'accounts@nexusretail.co.uk')->value('id');
        $item1Id = DB::table('items')->where('sku', 'SW-ERP-ENT')->value('id');
        $item2Id = DB::table('items')->where('sku', 'SRV-ARCH-HR')->value('id');

        if ($custId1 && !DB::table('invoices')->where('invoice_number', 'INV-2026-001')->exists()) {
            $invId1 = DB::table('invoices')->insertGetId([
                'invoice_number' => 'INV-2026-001',
                'customer_id' => $custId1,
                'invoice_date' => now()->subDays(10)->toDateString(),
                'due_date' => now()->addDays(20)->toDateString(),
                'subtotal' => 2400.00,
                'tax_total' => 432.00,
                'discount_total' => 0.00,
                'total' => 2832.00,
                'paid_amount' => 1000.00,
                'due_amount' => 1832.00,
                'status' => 'partial',
                'notes' => 'Thank you for partnering with WebotApp Technologies.',
                'terms' => 'Payment due within 30 days of invoice date.',
                'public_token' => Str::random(32),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(5),
            ]);

            DB::table('invoice_items')->insert([
                'invoice_id' => $invId1,
                'item_id' => $item1Id,
                'name' => 'Enterprise Cloud ERP License',
                'description' => '1 Year Subscription License for 50 Seats',
                'quantity' => 1,
                'price' => 2400.00,
                'tax_rate' => 18.00,
                'tax_amount' => 432.00,
                'total' => 2832.00,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);
        }

        if ($custId2 && !DB::table('invoices')->where('invoice_number', 'INV-2026-002')->exists()) {
            $invId2 = DB::table('invoices')->insertGetId([
                'invoice_number' => 'INV-2026-002',
                'customer_id' => $custId2,
                'invoice_date' => now()->subDays(5)->toDateString(),
                'due_date' => now()->addDays(25)->toDateString(),
                'subtotal' => 1200.00,
                'tax_total' => 216.00,
                'discount_total' => 50.00,
                'total' => 1366.00,
                'paid_amount' => 1366.00,
                'due_amount' => 0.00,
                'status' => 'paid',
                'notes' => 'Consulting services rendered for Q3 financial sync.',
                'terms' => 'Net 30 terms.',
                'public_token' => Str::random(32),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ]);

            DB::table('invoice_items')->insert([
                'invoice_id' => $invId2,
                'item_id' => $item2Id,
                'name' => 'Senior Architecture Consulting',
                'description' => '10 Hours Dedicated Engineering',
                'quantity' => 10,
                'price' => 120.00,
                'tax_rate' => 18.00,
                'tax_amount' => 216.00,
                'total' => 1366.00,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
        }

        // 10. Sample Transactions
        $bank1Id = DB::table('bank_accounts')->where('is_default', true)->value('id');
        $catHostingId = DB::table('categories')->where('name', 'Server & Cloud Hosting')->value('id');
        $vendorAwsId = DB::table('vendors')->where('name', 'Amazon Web Services (AWS)')->value('id');

        if ($bank1Id && !DB::table('transactions')->exists()) {
            DB::table('transactions')->insert([
                [
                    'type' => 'income',
                    'bank_account_id' => $bank1Id,
                    'to_bank_account_id' => null,
                    'customer_id' => $custId1,
                    'vendor_id' => null,
                    'invoice_id' => 1,
                    'bill_id' => null,
                    'category_id' => $catSalesId,
                    'amount' => 1000.00,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'TXN-HDFC-99120',
                    'transaction_date' => now()->subDays(5)->toDateString(),
                    'description' => 'Advance payment against INV-2026-001',
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5),
                ],
                [
                    'type' => 'income',
                    'bank_account_id' => $bank1Id,
                    'to_bank_account_id' => null,
                    'customer_id' => $custId2,
                    'vendor_id' => null,
                    'invoice_id' => 2,
                    'bill_id' => null,
                    'category_id' => $catConsultId,
                    'amount' => 1366.00,
                    'payment_method' => 'card',
                    'reference_number' => 'STRIPE-CH-882190',
                    'transaction_date' => now()->subDays(1)->toDateString(),
                    'description' => 'Full settlement for INV-2026-002',
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ],
                [
                    'type' => 'expense',
                    'bank_account_id' => $bank1Id,
                    'to_bank_account_id' => null,
                    'customer_id' => null,
                    'vendor_id' => $vendorAwsId,
                    'invoice_id' => null,
                    'bill_id' => null,
                    'category_id' => $catHostingId,
                    'amount' => 450.00,
                    'payment_method' => 'card',
                    'reference_number' => 'AWS-INV-77124',
                    'transaction_date' => now()->subDays(3)->toDateString(),
                    'description' => 'AWS Production Cluster & S3 Storage - September',
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ]
            ]);
        }
    }
}
