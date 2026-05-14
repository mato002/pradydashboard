@php
    $pageHeader = $header ?? null;
    $appDocumentTitle = __('Dashboard').' — '.config('app.name', 'Prady Dashboard');
@endphp

<x-prady-shell
    :header-slot="$pageHeader"
    :heading="null"
    :subheading="null"
    :document-title="$appDocumentTitle"
>
    {{ $slot }}
</x-prady-shell>
