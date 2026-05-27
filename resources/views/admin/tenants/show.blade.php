@php
    $shellUrl = route('tenants.show', $tenant);
@endphp

<x-dashboard-layout
    :heading="$tenant->company_name"
    :document-title="$tenant->company_name.' — '.__('Tenant workspace')"
>
    <div
        id="tenant-workspace-root"
        x-data="tenantWorkspace({
            baseUrl: @js($shellUrl),
            initialTab: @js($tab),
            tabs: @js(array_keys($workspaceTabs)),
        })"
        class="tenant-workspace-root min-w-0 max-w-full overflow-x-hidden"
    >
        @include('admin.tenants.partials.workspace.header')
        @include('admin.tenants.partials.workspace.metrics')
        @include('admin.tenants.partials.workspace.tabs')

        <div class="relative mt-6">
            <div
                x-show="loading"
                x-transition.opacity
                x-cloak
                class="absolute inset-0 z-10 rounded-2xl bg-white/70 backdrop-blur-[2px] dark:bg-slate-950/70"
            >
                @include('admin.tenants.partials.workspace.skeleton')
            </div>

            <div
                class="transition-opacity duration-200"
                :class="loading ? 'pointer-events-none opacity-40' : 'opacity-100'"
            >
                @include('admin.tenants.partials.workspace.content')
            </div>
        </div>
    </div>
</x-dashboard-layout>
