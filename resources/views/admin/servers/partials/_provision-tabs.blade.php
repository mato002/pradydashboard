@foreach (['identity', 'capacity', 'connectivity', 'security', 'billing', 'deployment'] as $tabId)
    <div
        x-show="panelVisible('{{ $tabId }}')"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="infra-provision-panel"
        role="tabpanel"
        :aria-hidden="!panelVisible('{{ $tabId }}')"
    >
        @include('admin.servers.partials.tabs.'.$tabId)
    </div>
@endforeach
