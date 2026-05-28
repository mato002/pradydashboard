<?php

namespace Tests\Feature;

use App\Jobs\Billing\GenerateFinancialDocumentPdfJob;
use App\Jobs\Billing\SendFinancialDocumentEmailJob;
use App\Support\Queue\QueueName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DocumentDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_job_targets_pdf_queue(): void
    {
        Queue::fake();

        GenerateFinancialDocumentPdfJob::dispatch(42, true);

        Queue::assertPushed(GenerateFinancialDocumentPdfJob::class, fn (GenerateFinancialDocumentPdfJob $job) => $job->queue === QueueName::PDF);
    }

    public function test_email_job_targets_emails_queue(): void
    {
        Queue::fake();

        SendFinancialDocumentEmailJob::dispatch(10, 20, 'tenant@example.com', false);

        Queue::assertPushed(SendFinancialDocumentEmailJob::class, fn (SendFinancialDocumentEmailJob $job) => $job->queue === QueueName::EMAILS);
    }
}
