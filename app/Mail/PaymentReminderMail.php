<?php

namespace App\Mail;

use App\Domain\Billing\BillingSettings;
use App\Models\TenantInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantInvoice $invoice,
        public ?string $pdfPath = null,
    ) {}

    public function envelope(): Envelope
    {
        $settings = app(BillingSettings::class);
        $company = $settings->companyLegalName() ?: config('app.name');

        return new Envelope(
            from: new Address($settings->billingFromEmail(), $company),
            subject: __(':company — Payment reminder :number', [
                'company' => $company,
                'number' => $this->invoice->invoice_number,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'settings' => app(BillingSettings::class),
                'clientName' => $this->invoice->clientDisplayName(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->pdfPath || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->pdfPath)
                ->as($this->invoice->invoice_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
