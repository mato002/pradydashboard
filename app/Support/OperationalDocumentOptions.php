<?php

namespace App\Support;

final class OperationalDocumentOptions
{
    /**
     * @return array<string, string>
     */
    public static function documentTypes(): array
    {
        return [
            'contract' => __('Contract'),
            'license_agreement' => __('License agreement'),
            'nda' => __('NDA'),
            'service_agreement' => __('Service agreement'),
            'onboarding_document' => __('Onboarding document'),
            'renewal_letter' => __('Renewal letter'),
            'invoice_attachment' => __('Invoice attachment'),
            'tax_document' => __('Tax document'),
            'other' => __('Other'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'draft' => __('Draft'),
            'pending_signature' => __('Pending signature'),
            'signed' => __('Signed'),
            'expired' => __('Expired'),
            'archived' => __('Archived'),
        ];
    }

    public static function contractTypes(): array
    {
        return ['contract', 'license_agreement', 'service_agreement'];
    }
}
