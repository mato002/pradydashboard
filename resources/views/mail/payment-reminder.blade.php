@php
    $company = $settings->companyLegalName() ?: config('app.name');
    $currency = $invoice->currency ?? 'KES';
@endphp
<x-mail::message>
# {{ __('Payment reminder') }}

{{ __('Hello :name,', ['name' => $clientName]) }}

{{ __('This is a friendly reminder that invoice **:number** has an outstanding balance.', [
    'number' => $invoice->invoice_number,
]) }}

**{{ __('Balance due') }}:** {{ $currency }} {{ number_format($invoice->balanceDue(), 2) }}

@if ($invoice->due_date)
**{{ __('Due date') }}:** {{ $invoice->due_date->format('M j, Y') }}
@endif

@if ($settings->paymentInstructions())
<x-mail::panel>
{!! nl2br(e($settings->paymentInstructions())) !!}
</x-mail::panel>
@endif

{{ __('If you have already paid, please disregard this message or reply with your payment reference.') }}

{{ __('Thank you,') }}  
{{ $company }}

<x-mail::subcopy>
{{ __('Sent from :app collections.', ['app' => config('app.name')]) }}
</x-mail::subcopy>
</x-mail::message>
