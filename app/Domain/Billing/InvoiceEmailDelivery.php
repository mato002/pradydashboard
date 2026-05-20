<?php

namespace App\Domain\Billing;

use App\Domain\Activity\ActivityLogger;
use App\Models\GeneratedDocument;
use App\Models\TenantInvoice;
use App\Support\ActivityLogCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceEmailDelivery
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function send(TenantInvoice $invoice, GeneratedDocument $document): bool
    {
        $email = $invoice->tenant?->billing_email;

        if (! $email) {
            $invoice->update(['delivery_status' => 'failed']);
            $document->update(['delivery_status' => 'failed']);

            return false;
        }

        try {
            Mail::raw(
                __('Please find your :type :number attached.', [
                    'type' => $invoice->document_type ?? 'invoice',
                    'number' => $invoice->invoice_number,
                ]),
                function ($message) use ($email, $invoice, $document): void {
                    $message->to($email)
                        ->subject(__(':app — :type :number', [
                            'app' => config('app.name'),
                            'type' => ucfirst((string) ($invoice->document_type ?? 'invoice')),
                            'number' => $invoice->invoice_number,
                        ]));

                    if ($document->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($document->pdf_path)) {
                        $message->attach(
                            \Illuminate\Support\Facades\Storage::disk('local')->path($document->pdf_path),
                            ['as' => $invoice->invoice_number.'.pdf']
                        );
                    }
                }
            );

            $now = now();
            $invoice->update([
                'email_delivered_at' => $now,
                'delivery_status' => 'sent',
            ]);
            $document->update([
                'email_sent_at' => $now,
                'delivery_status' => 'sent',
            ]);

            $this->activityLogger->log(
                'invoice.emailed',
                ActivityLogCategory::BILLING,
                __(':type :number emailed to :email', [
                    'type' => $invoice->document_type ?? 'invoice',
                    'number' => $invoice->invoice_number,
                    'email' => $email,
                ]),
                $invoice,
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning('Invoice email delivery failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            $invoice->update(['delivery_status' => 'failed']);
            $document->update(['delivery_status' => 'failed']);

            return false;
        }
    }
}
