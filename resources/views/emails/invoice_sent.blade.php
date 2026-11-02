<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f1f5f9; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 620px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 36px; background-color: #0f172a; color: #ffffff;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">
                                            {{ $company->name ?? 'WebotApp Enterprise' }}
                                        </h1>
                                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">
                                            Official Tax Invoice
                                        </p>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: rgba(255, 255, 255, 0.1); border-radius: 8px; font-size: 13px; font-weight: 700; font-family: monospace; color: #38bdf8;">
                                            {{ $invoice->invoice_number }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 36px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: #0f172a;">
                                Dear {{ $customer->name ?? 'Valued Customer' }},
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Please find below the invoice summary for your recent order. You can review the complete invoice details, download the receipt, or settle payment using the link below.
                            </p>

                            <!-- Summary Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; padding-bottom: 4px;">
                                                    Invoice Date
                                                </td>
                                                <td align="right" style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; padding-bottom: 4px;">
                                                    Due Date
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; font-weight: 600; color: #0f172a;">
                                                    {{ date('M d, Y', strtotime($invoice->invoice_date)) }}
                                                </td>
                                                <td align="right" style="font-size: 14px; font-weight: 600; color: #dc2626;">
                                                    {{ date('M d, Y', strtotime($invoice->due_date)) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-top: 16px; border-top: 1px dashed #cbd5e1; margin-top: 12px;">
                                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            <td style="font-size: 13px; color: #475569; font-weight: 600;">
                                                                Amount Due:
                                                            </td>
                                                            <td align="right" style="font-size: 22px; font-weight: 800; color: #059669;">
                                                                {{ $currencySymbol }}{{ number_format($invoice->due_amount > 0 ? $invoice->due_amount : $invoice->total, 2) }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Line Items Table -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e2e8f0;">
                                        <th align="left" style="padding: 10px 8px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Item / Description</th>
                                        <th align="center" style="padding: 10px 8px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Qty</th>
                                        <th align="right" style="padding: 10px 8px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Rate</th>
                                        <th align="right" style="padding: 10px 8px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $item)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 12px 8px; font-size: 13px; font-weight: 600; color: #1e293b;">
                                                {{ $item->name }}
                                                @if($item->description)
                                                    <div style="font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px;">{{ $item->description }}</div>
                                                @endif
                                            </td>
                                            <td align="center" style="padding: 12px 8px; font-size: 13px; color: #475569;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td align="right" style="padding: 12px 8px; font-size: 13px; color: #475569;">
                                                {{ $currencySymbol }}{{ number_format($item->price, 2) }}
                                            </td>
                                            <td align="right" style="padding: 12px 8px; font-size: 13px; font-weight: 700; color: #0f172a;">
                                                {{ $currencySymbol }}{{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Call to Action Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $viewUrl }}" target="_blank" style="display: inline-block; background-color: #059669; color: #ffffff; font-size: 14px; font-weight: 700; padding: 14px 32px; text-decoration: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);">
                                            View & Pay Invoice Online &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($invoice->notes))
                                <div style="padding: 14px 18px; background-color: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 6px; font-size: 12px; color: #475569; margin-bottom: 20px;">
                                    <strong>Notes:</strong> {{ $invoice->notes }}
                                </div>
                            @endif

                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #64748b;">
                                If you have any questions regarding this invoice, please contact us at 
                                <a href="mailto:{{ $company->email ?? 'billing@webotapp.com' }}" style="color: #0284c7; text-decoration: none;">
                                    {{ $company->email ?? 'billing@webotapp.com' }}
                                </a>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 36px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8;">
                            <p style="margin: 0 0 4px 0;">
                                {{ $company->name ?? 'WebotApp Enterprise' }} &bull; {{ $company->address ?? '' }} {{ $company->city ?? '' }} {{ $company->country ?? '' }}
                            </p>
                            <p style="margin: 0;">
                                Powered by WebotApp Accounting Software
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
