<?php

/**
 * Billing / document issuer defaults from environment.
 * Values here override stored platform.billing settings when non-empty.
 */
return [
    'company_legal_name' => env('BILLING_COMPANY_LEGAL_NAME', 'PRADY TECHNOLOGIES LIMITED'),
    'trading_name' => env('BILLING_TRADING_NAME', 'Prady Technologies'),
    'tax_pin' => env('BILLING_TAX_PIN', 'P051769315N'),
    'issuer_phone' => env('BILLING_ISSUER_PHONE', '0110 098 548 / 0722 295 194'),
    'issuer_email' => env('BILLING_ISSUER_EMAIL', 'info@pradytec.com'),
    'issuer_website' => env('BILLING_ISSUER_WEBSITE', 'www.pradytec.com'),
    'issuer_address' => env('BILLING_ISSUER_ADDRESS', 'Nakuru, Barnabas, Opp. Epic Academy'),
    'issuer_tagline' => env('BILLING_ISSUER_TAGLINE', 'DOING I.T DIFFERENTLY'),
    'billing_from_email' => env('BILLING_FROM_EMAIL', env('MAIL_FROM_ADDRESS', 'info@pradytecai.com')),
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'KES'),
    'numbering_style' => env('BILLING_NUMBERING_STYLE', 'short'),
    'bank_name' => env('BILLING_BANK_NAME', 'Absa bank'),
    'bank_account_name' => env('BILLING_BANK_ACCOUNT_NAME', 'P technologies Ltd'),
    'bank_account_number' => env('BILLING_BANK_ACCOUNT_NUMBER', '2040790635'),
    'bank_branch' => env('BILLING_BANK_BRANCH', 'Gilgil'),
    'mpesa_paybill' => env('BILLING_MPESA_PAYBILL', '918885'),
    // Legacy platform default; documents derive paybill account from each client name instead.
    'paybill_account_number' => env('BILLING_PAYBILL_ACCOUNT', 'PAGECAPITAL'),
    'document_brand_header_path' => env('BILLING_DOCUMENT_HEADER_PATH'),
    'document_brand_footer_path' => env('BILLING_DOCUMENT_FOOTER_PATH'),
];
