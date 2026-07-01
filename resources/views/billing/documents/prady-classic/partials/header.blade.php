@php
    use App\Support\Billing\PradyClassicBrandAssets;

    $headerSrc = PradyClassicBrandAssets::headerSrc();
@endphp

@if ($headerSrc)
    <img src="{{ $headerSrc }}" class="prady-header-image" alt="">
@else
    @include('billing.documents.prady-classic.partials.header-brand-fallback')
@endif

<table class="pc-contact-row" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="pc-contact-left" width="50%" valign="top">
            @if (($issuer['phone'] ?? '') !== '')
                <div>Tel: {{ $issuer['phone'] }}</div>
            @endif
            @if (($issuer['address'] ?? '') !== '')
                <div>{{ $issuer['address'] }}</div>
            @endif
        </td>
        <td class="pc-contact-right" width="50%" valign="top" align="right">
            @if (($issuer['email'] ?? '') !== '')
                <div>Email: {{ $issuer['email'] }}</div>
            @endif
            @if (($issuer['website'] ?? '') !== '')
                <div>Website: {{ $issuer['website'] }}</div>
            @endif
        </td>
    </tr>
</table>

<div class="pc-contact-rule">&nbsp;</div>
