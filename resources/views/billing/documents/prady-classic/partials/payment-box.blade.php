@if (($pay['bank_name'] ?? '') !== '')
    <div>Bank: {{ $pay['bank_name'] }}</div>
@endif
@if (($pay['account_number'] ?? $pay['bank_account_number'] ?? '') !== '')
    <div>{{ __('Account number') }}: {{ $pay['account_number'] ?? $pay['bank_account_number'] }}</div>
@endif
@if (($pay['account_name'] ?? '') !== '' || ($pay['bank_branch'] ?? '') !== '')
    <div>
        {{ trim(($pay['account_name'] ?? '').' '.($pay['bank_branch'] ?? '').' branch') }}
        @if (($pay['paybill'] ?? $pay['mpesa_paybill'] ?? '') !== '' || ($pay['paybill_account'] ?? $pay['paybill_account_number'] ?? '') !== '')
            or
        @endif
    </div>
@endif
@if (($pay['paybill'] ?? $pay['mpesa_paybill'] ?? '') !== '')
    <div>{{ __('Paybill') }}: {{ $pay['paybill'] ?? $pay['mpesa_paybill'] }}</div>
@endif
@if (($pay['paybill_account'] ?? $pay['paybill_account_number'] ?? '') !== '')
    <div>{{ __('Account') }}: <span class="pc-paybill-account-name">{{ $pay['paybill_account'] ?? $pay['paybill_account_number'] }}</span></div>
@endif
@if (! empty($pay['payment_instructions'] ?? null))
    <div>{{ $pay['payment_instructions'] }}</div>
@endif
