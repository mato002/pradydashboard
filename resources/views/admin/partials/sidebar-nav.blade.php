@php
    use App\Support\Rbac\Rbac;

    $infraActive = request()->routeIs('servers.*', 'hosted-projects.*', 'projects.*', 'products.*', 'ssl-domains.*', 'backups.*', 'server-health.*');
    $tenantActive = request()->routeIs('tenants.*', 'subscriptions.*', 'license-logs.*', 'access-controls.*');
    $financialActive = request()->routeIs('invoices.*', 'payments.*') && ! request()->routeIs('settings.payments-gateway.*');
    $opsActive = request()->routeIs('deployments.*', 'monitoring.*', 'risk-center.*', 'activity-logs.*', 'support-tickets.*');
    $settingsActive = request()->routeIs('access-control.*', 'users-roles.*', 'system-settings.*', 'api-credentials.*', 'settings.payments-gateway.*', 'integration-setup-guide.*');
    $guideActive = request()->routeIs('integration-setup-guide.*');
@endphp

<nav class="flex flex-1 flex-col gap-1 px-2 text-[13px] font-medium">
    @permission('dashboard.view')
        <x-admin.sidebar-link
            :href="route('dashboard')"
            :label="__('Overview')"
            :active="request()->routeIs('dashboard')"
            icon-name="gauge-high"
        />
    @endpermission

    @if(Rbac::can('servers.view') || Rbac::can('projects.view') || Rbac::can('ssl.view') || Rbac::can('backups.view') || Rbac::can('server_health.view'))
        <x-admin.sidebar-group id="infrastructure" :label="__('Infrastructure')" :default-open="$infraActive" icon-name="network-wired">
            @permission('servers.view')
                <x-admin.sidebar-link :href="route('servers.index')" :label="__('Servers')" :active="request()->routeIs('servers.*')" icon-name="server" nested />
            @endpermission
            @permission('projects.view')
                <x-admin.sidebar-link :href="route('hosted-projects.index')" :label="__('Hosted Projects')" :active="request()->routeIs('hosted-projects.*', 'projects.*')" icon-name="layer-group" nested />
                <x-admin.sidebar-link :href="route('products.index')" :label="__('Products')" :active="request()->routeIs('products.*')" icon-name="box" nested />
            @endpermission
            @permission('ssl.view')
                <x-admin.sidebar-link :href="route('ssl-domains.index')" :label="__('SSL & Domains')" :active="request()->routeIs('ssl-domains.*')" icon-name="shield-halved" nested />
            @endpermission
            @permission('backups.view')
                <x-admin.sidebar-link :href="route('backups.index')" :label="__('Backups')" :active="request()->routeIs('backups.*')" icon-name="database" nested />
            @endpermission
            @permission('server_health.view')
                <x-admin.sidebar-link :href="route('server-health.index')" :label="__('Server Health')" :active="request()->routeIs('server-health.*')" icon-name="heart-pulse" nested />
            @endpermission
        </x-admin.sidebar-group>
    @endif

    @if(Rbac::can('tenants.view') || Rbac::can('subscriptions.view') || Rbac::can('license_logs.view') || Rbac::can('tenant_access_controls.view'))
        <x-admin.sidebar-group id="tenants" :label="__('Tenants')" :default-open="$tenantActive" icon-name="users">
            @permission('tenants.view')
                <x-admin.sidebar-link :href="route('tenants.index')" :label="__('All tenants')" :active="request()->routeIs('tenants.*')" nested />
            @endpermission
            @permission('subscriptions.view')
                <x-admin.sidebar-link :href="route('subscriptions.index')" :label="__('Subscriptions')" :active="request()->routeIs('subscriptions.*')" icon-name="credit-card" nested />
            @endpermission
            @permission('license_logs.view')
                <x-admin.sidebar-link :href="route('license-logs.index')" :label="__('License Logs')" :active="request()->routeIs('license-logs.*')" icon-name="key" nested />
            @endpermission
            @permission('tenant_access_controls.view')
                <x-admin.sidebar-link :href="route('access-controls.index')" :label="__('Access Controls')" :active="request()->routeIs('access-controls.*')" icon-name="lock" nested />
            @endpermission
        </x-admin.sidebar-group>
    @endif

    @if(Rbac::can('invoices.view') || Rbac::can('payments.view'))
        <x-admin.sidebar-group id="financials" :label="__('Financials')" :default-open="$financialActive" icon-name="file-invoice-dollar">
            @permission('invoices.view')
                <x-admin.sidebar-link :href="route('invoices.index')" :label="__('Invoices')" :active="request()->routeIs('invoices.*')" icon-name="file-invoice" nested />
            @endpermission
            @permission('payments.view')
                <x-admin.sidebar-link :href="route('payments.index')" :label="__('Payments')" :active="request()->routeIs('payments.*')" icon-name="money-bill-wave" nested />
            @endpermission
        </x-admin.sidebar-group>
    @endif

    @if(Rbac::can('deployments.view') || Rbac::can('monitoring.view') || Rbac::can('risk_center.view') || Rbac::can('activity_logs.view') || Rbac::can('support.tickets.view'))
        <x-admin.sidebar-group id="operations" :label="__('Operations')" :default-open="$opsActive" icon-name="chart-column">
            @permission('deployments.view')
                <x-admin.sidebar-link :href="route('deployments.index')" :label="__('Deployments')" :active="request()->routeIs('deployments.*')" icon-name="rocket" nested />
            @endpermission
            @permission('monitoring.view')
                <x-admin.sidebar-link :href="route('monitoring.index')" :label="__('Monitoring')" :active="request()->routeIs('monitoring.index')" icon-name="chart-line" nested />
                <x-admin.sidebar-link :href="route('monitoring.queues')" :label="__('Redis & Queues')" :active="request()->routeIs('monitoring.queues')" icon-name="list-ul" nested />
            @endpermission
            @permission('risk_center.view')
                <x-admin.sidebar-link :href="route('risk-center.index')" :label="__('Risk Center')" :active="request()->routeIs('risk-center.*')" icon-name="triangle-exclamation" nested />
            @endpermission
            @permission('activity_logs.view')
                <x-admin.sidebar-link :href="route('activity-logs.index')" :label="__('Activity Logs')" :active="request()->routeIs('activity-logs.*')" icon-name="clock" nested />
            @endpermission
            @permission('support.tickets.view')
                <x-admin.sidebar-link :href="route('support-tickets.index')" :label="__('Support Tickets')" :active="request()->routeIs('support-tickets.*')" icon-name="life-ring" nested />
            @endpermission
        </x-admin.sidebar-group>
    @endif

    @permission('hr.staff.view')
        <x-admin.sidebar-link
            :href="route('hr.index')"
            :label="__('HR & Team')"
            :active="request()->routeIs('hr.*')"
            icon-name="user-tie"
        />
    @endpermission

    @permission('dashboard.view')
        <x-admin.sidebar-link
            :href="route('integration-setup-guide.index')"
            :label="__('Integration Setup Guide')"
            :active="$guideActive"
            icon-name="book"
        />
    @endpermission

    <x-admin.sidebar-group id="settings" :label="__('Settings')" :default-open="$settingsActive" icon-name="gear">
        @permission('rbac.manage')
            <x-admin.sidebar-link :href="route('access-control.permissions.index')" :label="__('Access Control')" :active="request()->routeIs('access-control.*')" nested />
        @endpermission
        <x-admin.sidebar-link :href="route('users-roles.index')" :label="__('Users & Roles')" :active="request()->routeIs('users-roles.*')" nested />
        @permission('system_settings.update')
            <x-admin.sidebar-link :href="route('system-settings.edit')" :label="__('System Settings')" :active="request()->routeIs('system-settings.*')" nested />
        @endpermission
        @permission('api_credentials.view')
            <x-admin.sidebar-link :href="route('api-credentials.index')" :label="__('API & Integrations')" :active="request()->routeIs('api-credentials.*')" nested />
        @endpermission
        @permission('payments_gateway.view')
            <x-admin.sidebar-link
                :href="route('settings.payments-gateway.overview')"
                :label="__('Payments Gateway')"
                :active="request()->routeIs('settings.payments-gateway.*')"
                nested
            />
        @endpermission
    </x-admin.sidebar-group>
</nav>
