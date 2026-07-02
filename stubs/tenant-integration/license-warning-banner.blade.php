{{-- Copy to resources/views/partials/prady-license-warning.blade.php --}}
{{-- Include in your main layout: @include('partials.prady-license-warning') --}}
@if (session('prady_license_warning'))
    <div role="alert" style="background:#fef3c7;color:#92400e;padding:0.75rem 1rem;text-align:center;font-size:0.875rem;border-bottom:1px solid #fcd34d;">
        {{ session('prady_license_warning') }}
    </div>
@endif
