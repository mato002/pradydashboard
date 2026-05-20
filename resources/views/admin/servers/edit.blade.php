<x-dashboard-layout :heading="__('Edit server')" :subheading="__('Update server configuration')">
    <x-admin.form-shell
        :title="$server->name"
        :subtitle="__('Capacity, connectivity, billing, and WHM settings.')"
        :badge="__('Infrastructure')"
        :back-href="route('servers.show', $server)"
        :back-label="__('Back to server')"
    >
        <form method="post" action="{{ route('servers.update', $server) }}" id="server-form" class="max-w-4xl space-y-5">
            @csrf
            @method('put')
            @include('admin.servers.partials._form-tabs', [
                'server' => $server,
                'submitLabel' => __('Save changes'),
            ])
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
