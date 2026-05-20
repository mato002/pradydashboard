<?php

namespace App\Support;

class StaffDocumentOptions
{
    /**
     * @return array<string, string>
     */
    public static function documentTypes(): array
    {
        return [
            'contract' => __('Contract'),
            'nda' => __('NDA'),
            'id' => __('ID'),
            'cv' => __('CV'),
            'certificate' => __('Certificate'),
            'appointment_letter' => __('Appointment letter'),
            'warning_letter' => __('Warning letter'),
            'exit_document' => __('Exit document'),
            'other' => __('Other'),
        ];
    }
}
