@props([
    'heading' => null,
    'subheading' => null,
])

<x-prady-shell :heading="$heading" :subheading="$subheading">
    {{ $slot }}
</x-prady-shell>
