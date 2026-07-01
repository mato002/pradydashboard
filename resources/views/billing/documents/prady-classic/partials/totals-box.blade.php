@if ($isReceipt)
    <div class="pc-total-line">
        <span class="pc-total-label">{{ __('PAID') }}:</span>
        <span class="pc-total-amount">{{ $fmtKsh($paid) }}</span>
    </div>
    <div class="pc-total-line pc-balance-line">
        <span class="pc-total-label">{{ __('BALANCE') }}:</span>
        <span class="pc-total-amount">{{ $fmtKsh($balance) }}</span>
    </div>
@elseif ($isQuotation)
    <div class="pc-total-line">
        <span class="pc-total-label">{{ __('Total') }}:</span>
        <span class="pc-total-amount">{{ $fmtKsh($total) }}</span>
    </div>
@else
    @if ($discount > 0)
        <div class="pc-total-line">
            <span class="pc-total-label">{{ __('Discount') }}:</span>
            <span class="pc-total-amount">{{ $fmtKsh($discount) }}</span>
        </div>
    @endif
    @if ($tax > 0)
        <div class="pc-total-line">
            <span class="pc-total-label">{{ __('Tax') }}:</span>
            <span class="pc-total-amount">{{ $fmtKsh($tax) }}</span>
        </div>
    @endif
    <div class="pc-total-line">
        <span class="pc-total-label">{{ __('Subtotal') }}:</span>
        <span class="pc-total-amount">{{ $fmtKsh($subtotal) }}</span>
    </div>
    <div class="pc-total-line">
        <span class="pc-total-label">{{ __('Grand Total') }}:</span>
        <span class="pc-total-amount">{{ $fmtKsh($total) }}</span>
    </div>
    @if ($showPaidBalance)
        <div class="pc-total-line">
            <span class="pc-total-label">{{ __('PAID') }}:</span>
            <span class="pc-total-amount">{{ $fmtKsh($paid) }}</span>
        </div>
        <div class="pc-total-line pc-balance-line">
            <span class="pc-total-label">{{ __('BALANCE') }}:</span>
            <span class="pc-total-amount">{{ $fmtKsh($balance) }}</span>
        </div>
    @endif
@endif
