<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Database\Seeder;

class PradyClassicA5TemplateSeeder extends Seeder
{
    /**
     * Prady Classic A5 — official printed-form layout for financial documents.
     */
    public function run(): void
    {
        $cssPath = resource_path('views/billing/documents/prady-classic/document.css');
        $css = is_file($cssPath) ? file_get_contents($cssPath) : '';

        $branding = [
            'footer_text' => null,
            'logo_url' => null,
        ];

        $types = [
            BillingDocumentType::INVOICE => 'Prady Classic A5 Invoice',
            BillingDocumentType::PROFORMA => 'Prady Classic A5 Proforma',
            BillingDocumentType::QUOTATION => 'Prady Classic A5 Quotation',
            BillingDocumentType::RECEIPT => 'Prady Classic A5 Receipt',
            BillingDocumentType::STATEMENT => 'Prady Classic A5 Statement',
            BillingDocumentType::CREDIT_NOTE => 'Prady Classic A5 Credit Note',
            BillingDocumentType::DEBIT_NOTE => 'Prady Classic A5 Debit Note',
        ];

        foreach ($types as $type => $name) {
            DocumentTemplate::query()->where('type', $type)->update(['is_default' => false]);
        }

        foreach ($types as $type => $name) {
            DocumentTemplate::query()->updateOrCreate(
                [
                    'type' => $type,
                    'style' => 'prady_classic_a5',
                ],
                [
                    'name' => $name,
                    'blade_view' => 'billing.documents.prady-classic-a5',
                    'paper_size' => 'A5',
                    'orientation' => 'portrait',
                    'active' => true,
                    'is_default' => in_array($type, [
                        BillingDocumentType::INVOICE,
                        BillingDocumentType::PROFORMA,
                        BillingDocumentType::QUOTATION,
                        BillingDocumentType::RECEIPT,
                        BillingDocumentType::STATEMENT,
                    ], true),
                    'css' => $css,
                    'branding' => $branding,
                ],
            );
        }
    }
}
