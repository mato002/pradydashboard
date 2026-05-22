@php
    $company = $settings->companyLegalName() ?: config('app.name');
    $currency = $invoice->currency ?? 'KES';
@endphp
<x-mail::message>
@if ($isResend)
# {{ __('Resent document') }}
@else
# {{ __('Your :type', ['type' => $typeLabel]) }}
@endif

{{ __('Hello :name,', ['name' => $clientName]) }}

{{ __('Please find attached :type **:number** for :amount.', [
    'type' => strtolower($typeLabel),
    'number' => $invoice->invoice_number,
    'amount' => $currency.' '.number_format($invoice->invoiceTotal(), 2),
]) }}

@if ($invoice->due_date && $invoice->document_type === 'invoice')
{{ __('Due date') }}: **{{ $invoice->due_date->format('M j, Y') }}**
@endif

@if ($settings->paymentInstructions())
<x-mail::panel>
{!! nl2br(e($settings->paymentInstructions())) !!}
</x-mail::panel>
@endif

@if ($settings->invoiceFooterNotes())
{{ $settings->invoiceFooterNotes() }}
@endif

{{ __('Thank you for your business.') }}

{{ $company }}
@if ($settings->taxPin())
{{ __('PIN') }}: {{ $settings->taxPin() }}
@endif

<x-mail::subcopy>
{{ __('This message was sent from :app billing.', ['app' => config('app.name')]) }}
</x-mail::subcopy>
</x-mail::message>
