@php
    use App\Support\Billing\PradyClassicBrandAssets;

    $footerSrc = PradyClassicBrandAssets::footerSrc();
@endphp
<div class="pc-doc-footer">
    <div class="pc-thanks">Thank you.</div>
    @if ($footerSrc)
        <img src="{{ $footerSrc }}" class="prady-footer-image pc-footer-wave" alt="">
    @else
        @include('billing.documents.prady-classic.partials.footer-wave-fallback')
    @endif
</div>
