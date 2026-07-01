<div class="pc-quotation-note">
    <div class="pc-quotation-note-title">{{ __('Prepared for approval') }}</div>
    <div>{{ __('Please sign and return to confirm acceptance of this quotation.') }}</div>
    @if (! empty($s['notes'] ?? null))
        <div style="margin-top:4px;">{{ $s['notes'] }}</div>
    @endif
</div>
