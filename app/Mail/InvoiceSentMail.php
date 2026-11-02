<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public Company $company;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, ?Company $company = null)
    {
        $this->invoice = $invoice;
        $this->company = $company ?? (Company::first() ?? new Company([
            'name' => config('app.name', 'WebotApp Accounting'),
            'currency_symbol' => '₹',
        ]));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromEmail = !empty($this->company->email) ? $this->company->email : config('mail.from.address', 'billing@webotapp.com');
        $fromName = !empty($this->company->name) ? $this->company->name : config('mail.from.name', 'Billing Department');

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: "Invoice #{$this->invoice->invoice_number} from {$fromName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_sent',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->company,
                'customer' => $this->invoice->customer,
                'currencySymbol' => $this->company->currency_symbol ?? '₹',
                'viewUrl' => route('invoices.public', $this->invoice->public_token),
            ],
        );
    }
}
