<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Support\Billing\BillingDocumentType;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            'modern_saas' => 'billing.documents.modern-saas',
            'classic_corporate' => 'billing.documents.classic-corporate',
            'thermal_receipt' => 'billing.documents.thermal-receipt',
            'compact_mobile' => 'billing.documents.compact-mobile',
        ];

        foreach (BillingDocumentType::all() as $type) {
            $isDefault = $type === BillingDocumentType::INVOICE;

            DocumentTemplate::query()->firstOrCreate(
                [
                    'type' => $type,
                    'style' => 'modern_saas',
                ],
                [
                    'name' => BillingDocumentType::label($type).' — '.__('Modern SaaS'),
                    'blade_view' => $styles['modern_saas'],
                    'paper_size' => $type === BillingDocumentType::RECEIPT ? 'A5' : 'A4',
                    'orientation' => 'portrait',
                    'active' => true,
                    'is_default' => $isDefault,
                    'branding' => [
                        'primary_color' => '#4f46e5',
                        'accent_color' => '#f59e0b',
                        'show_qr' => false,
                        'footer_text' => null,
                        'watermark' => null,
                        'signature_label' => __('Authorized signature'),
                    ],
                ],
            );
        }
    }
}
