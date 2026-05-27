@props([
    'heading' => null,
    'subheading' => null,
    'documentTitle' => null,
])

@php
    use App\Support\Admin\PradyWorkspaceRequest;
@endphp

@if (PradyWorkspaceRequest::isPartial(request()))
    <x-prady-workspace-content :heading="$heading" :subheading="$subheading" :document-title="$documentTitle">
        {{ $slot }}
    </x-prady-workspace-content>
@else
    <x-prady-shell :heading="$heading" :subheading="$subheading" :document-title="$documentTitle">
        <x-prady-workspace-content :heading="$heading" :subheading="$subheading" :document-title="$documentTitle">
            {{ $slot }}
        </x-prady-workspace-content>
    </x-prady-shell>
@endif
