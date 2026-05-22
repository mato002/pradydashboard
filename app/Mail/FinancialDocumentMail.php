<?php

namespace App\Mail;

use App\Domain\Billing\BillingSettings;
use App\Models\TenantInvoice;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FinancialDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantInvoice $invoice,
        public string $pdfPath,
        public bool $isResend = false,
    ) {}

    public function envelope(): Envelope
    {
        $settings = app(BillingSettings::class);
        $typeLabel = BillingDocumentType::label($this->invoice->document_type ?? 'invoice');
        $company = $settings->companyLegalName() ?: config('app.name');

        return new Envelope(
            from: new Address(
                $settings->billingFromEmail(),
                $company,
            ),
            subject: $this->isResend
                ? __(':company — :type :number (resent)', [
                    'company' => $company,
                    'type' => $typeLabel,
                    'number' => $this->invoice->invoice_number,
                ])
                : __(':company — :type :number', [
                    'company' => $company,
                    'type' => $typeLabel,
                    'number' => $this->invoice->invoice_number,
                ]),
        );
    }

    public function content(): Content
    {
        $settings = app(BillingSettings::class);

        return new Content(
            markdown: 'mail.financial-document',
            with: [
                'invoice' => $this->invoice,
                'settings' => $settings,
                'isResend' => $this->isResend,
                'clientName' => $this->invoice->clientDisplayName(),
                'typeLabel' => BillingDocumentType::label($this->invoice->document_type ?? 'invoice'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! Storage::disk('local')->exists($this->pdfPath)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $this->pdfPath)
                ->as($this->invoice->invoice_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
