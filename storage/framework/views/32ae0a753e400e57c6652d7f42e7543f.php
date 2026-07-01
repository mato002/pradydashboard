<?php
    use App\Support\Rbac\Rbac;

    $iconServer = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0A2.25 2.25 0 013.75 12V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25V12a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>';
    $iconProject = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15a2.25 2.25 0 012.25 2.25v.75m-18 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-18 0v-1.5A2.25 2.25 0 014.5 9h15a2.25 2.25 0 012.25 2.25v1.5" /></svg>';
    $iconProduct = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m13.5 0V12a2.25 2.25 0 002.25 2.25M3.75 19.5h19.5a2.25 2.25 0 00-2.25-2.25V5.25a2.25 2.25 0 012.25-2.25h13.5a2.25 2.25 0 012.25 2.25v14.25a2.25 2.25 0 01-2.25 2.25m-13.5 0h13.5" /></svg>';
    $iconSsl = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286zm0 13.036h.008v.008H12v-.008z" /></svg>';
    $iconBackup = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>';
    $iconHealth = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-3.75v1.5" /></svg>';
    $iconTenant = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>';
    $iconSub = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3 3.75h10.5a2.25 2.25 0 002.25-2.25V15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 15v3.75A2.25 2.25 0 006.75 21z" /></svg>';
    $iconLicense = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>';
    $iconLock = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>';
    $iconInvoice = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>';
    $iconPayment = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .621-.504 1.125-1.125 1.125H3.75m19.5-1.5H21M3.75 20.25h17.25m-17.25-3h17.25m-17.25-3h17.25" /></svg>';
    $iconDeploy = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a6 6 0 001.666 5.022" /></svg>';
    $iconMonitor = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>';
    $iconRisk = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>';
    $iconActivity = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    $iconSupport = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a23.922 23.922 0 0112.204 3.66c1.115.885 1.51 2.316.94 3.572a3.105 3.105 0 01-.037.043 12.404 12.404 0 01-4.255 1.5c-.188.028-.377.052-.566.075-1.456.194-2.911.292-4.371.292-1.39 0-2.781-.085-4.163-.254a12.404 12.404 0 01-4.255-1.5 3.105 3.105 0 01-.037-.043.75.75 0 01.631-1.151 23.922 23.922 0 0012.204-3.66.75.75 0 01.631.151c.41.314.68.81.68 1.362 0 .548-.27 1.048-.68 1.362z" /></svg>';
    $iconHr = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772M15 12.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';
    $iconSettings = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';
    $iconOverview = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-6 10.5 6M4.5 19.5h15" /></svg>';
    $iconInfraGroup = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03L19.5 12.75M21.75 17.25l-2.25 2.25M21.75 17.25l-2.25-2.25M3.75 17.25l2.25 2.25M3.75 17.25l2.25-2.25M3.75 17.25v-.228a4.5 4.5 0 01.12-1.03L4.5 12.75M3.75 17.25h16.5M4.5 12.75V9.75a2.25 2.25 0 012.25-2.25h10.5A2.25 2.25 0 0119.5 9.75v3" /></svg>';

    $infraActive = request()->routeIs('servers.*', 'hosted-projects.*', 'projects.*', 'products.*', 'ssl-domains.*', 'backups.*', 'server-health.*');
    $tenantActive = request()->routeIs('tenants.*', 'subscriptions.*', 'license-logs.*', 'access-controls.*');
    $financialActive = request()->routeIs('invoices.*', 'payments.*') && ! request()->routeIs('settings.payments-gateway.*');
    $opsActive = request()->routeIs('deployments.*', 'monitoring.*', 'risk-center.*', 'activity-logs.*', 'support-tickets.*');
    $settingsActive = request()->routeIs('access-control.*', 'users-roles.*', 'system-settings.*', 'api-credentials.*', 'settings.payments-gateway.*');

    $paymentsGatewayMonitoringLinks = [
        ['route' => 'settings.payments-gateway.operations-console', 'label' => __('Operations Console'), 'pattern' => 'settings.payments-gateway.operations-console'],
        ['route' => 'settings.payments-gateway.transactions.index', 'label' => __('Transactions'), 'pattern' => 'settings.payments-gateway.transactions.*'],
        ['route' => 'settings.payments-gateway.callback-logs.index', 'label' => __('Callback Logs'), 'pattern' => 'settings.payments-gateway.callback-logs.*'],
        ['route' => 'settings.payments-gateway.webhook-events.index', 'label' => __('Webhook Events'), 'pattern' => 'settings.payments-gateway.webhook-events.*'],
        ['route' => 'settings.payments-gateway.webhook-deliveries.index', 'label' => __('Webhook Deliveries'), 'pattern' => 'settings.payments-gateway.webhook-deliveries.*'],
        ['route' => 'settings.payments-gateway.production-readiness', 'label' => __('Production Readiness'), 'pattern' => 'settings.payments-gateway.production-readiness'],
        ['route' => 'settings.payments-gateway.go-live-dry-run', 'label' => __('Go-Live Dry Run'), 'pattern' => 'settings.payments-gateway.go-live-dry-run'],
    ];
?>

<nav class="flex flex-1 flex-col gap-1 px-2 text-[13px] font-medium">
    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'dashboard.view')): ?>
        <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('dashboard'),'label' => __('Overview'),'active' => request()->routeIs('dashboard'),'icon' => $iconOverview]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('dashboard')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Overview')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconOverview)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(Rbac::can('servers.view') || Rbac::can('projects.view') || Rbac::can('ssl.view') || Rbac::can('backups.view') || Rbac::can('server_health.view')): ?>
        <?php if (isset($component)) { $__componentOriginal19667d6231f8d2a7fc135a984252ca90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19667d6231f8d2a7fc135a984252ca90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'infrastructure','label' => __('Infrastructure'),'defaultOpen' => $infraActive,'icon' => $iconInfraGroup]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'infrastructure','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Infrastructure')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($infraActive),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconInfraGroup)]); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'servers.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('servers.index'),'label' => __('Servers'),'active' => request()->routeIs('servers.*'),'icon' => $iconServer,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('servers.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Servers')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('servers.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconServer),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'projects.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('hosted-projects.index'),'label' => __('Hosted Projects'),'active' => request()->routeIs('hosted-projects.*', 'projects.*'),'icon' => $iconProject,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted Projects')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hosted-projects.*', 'projects.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconProject),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('products.index'),'label' => __('Products'),'active' => request()->routeIs('products.*'),'icon' => $iconProduct,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('products.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Products')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('products.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconProduct),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'ssl.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('ssl-domains.index'),'label' => __('SSL & Domains'),'active' => request()->routeIs('ssl-domains.*'),'icon' => $iconSsl,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ssl-domains.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SSL & Domains')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('ssl-domains.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconSsl),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'backups.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('backups.index'),'label' => __('Backups'),'active' => request()->routeIs('backups.*'),'icon' => $iconBackup,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('backups.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Backups')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('backups.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconBackup),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'server_health.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('server-health.index'),'label' => __('Server Health'),'active' => request()->routeIs('server-health.*'),'icon' => $iconHealth,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('server-health.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Server Health')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('server-health.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHealth),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $attributes = $__attributesOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $component = $__componentOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__componentOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(Rbac::can('tenants.view') || Rbac::can('subscriptions.view') || Rbac::can('license_logs.view') || Rbac::can('tenant_access_controls.view')): ?>
        <?php if (isset($component)) { $__componentOriginal19667d6231f8d2a7fc135a984252ca90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19667d6231f8d2a7fc135a984252ca90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'tenants','label' => __('Tenants'),'defaultOpen' => $tenantActive,'icon' => $iconTenant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'tenants','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tenants')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenantActive),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconTenant)]); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'tenants.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('tenants.index'),'label' => __('All tenants'),'active' => request()->routeIs('tenants.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('tenants.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('All tenants')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('tenants.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'subscriptions.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('subscriptions.index'),'label' => __('Subscriptions'),'active' => request()->routeIs('subscriptions.*'),'icon' => $iconSub,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('subscriptions.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subscriptions')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('subscriptions.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconSub),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'license_logs.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('license-logs.index'),'label' => __('License Logs'),'active' => request()->routeIs('license-logs.*'),'icon' => $iconLicense,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('license-logs.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('License Logs')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('license-logs.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconLicense),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'tenant_access_controls.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('access-controls.index'),'label' => __('Access Controls'),'active' => request()->routeIs('access-controls.*'),'icon' => $iconLock,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('access-controls.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access Controls')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('access-controls.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconLock),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $attributes = $__attributesOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $component = $__componentOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__componentOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(Rbac::can('invoices.view') || Rbac::can('payments.view')): ?>
        <?php if (isset($component)) { $__componentOriginal19667d6231f8d2a7fc135a984252ca90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19667d6231f8d2a7fc135a984252ca90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'financials','label' => __('Financials'),'defaultOpen' => $financialActive,'icon' => $iconInvoice]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'financials','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Financials')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($financialActive),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconInvoice)]); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'invoices.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('invoices.index'),'label' => __('Invoices'),'active' => request()->routeIs('invoices.*'),'icon' => $iconInvoice,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Invoices')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('invoices.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconInvoice),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'payments.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('payments.index'),'label' => __('Payments'),'active' => request()->routeIs('payments.*'),'icon' => $iconPayment,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('payments.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('payments.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconPayment),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $attributes = $__attributesOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $component = $__componentOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__componentOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(Rbac::can('deployments.view') || Rbac::can('monitoring.view') || Rbac::can('risk_center.view') || Rbac::can('activity_logs.view') || Rbac::can('support.tickets.view')): ?>
        <?php if (isset($component)) { $__componentOriginal19667d6231f8d2a7fc135a984252ca90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19667d6231f8d2a7fc135a984252ca90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'operations','label' => __('Operations'),'defaultOpen' => $opsActive,'icon' => $iconMonitor]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'operations','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Operations')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opsActive),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconMonitor)]); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'deployments.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('deployments.index'),'label' => __('Deployments'),'active' => request()->routeIs('deployments.*'),'icon' => $iconDeploy,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('deployments.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Deployments')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('deployments.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconDeploy),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'monitoring.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('monitoring.index'),'label' => __('Monitoring'),'active' => request()->routeIs('monitoring.index'),'icon' => $iconMonitor,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitoring')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('monitoring.index')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconMonitor),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('monitoring.queues'),'label' => __('Redis & Queues'),'active' => request()->routeIs('monitoring.queues'),'icon' => $iconMonitor,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.queues')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Redis & Queues')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('monitoring.queues')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconMonitor),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'risk_center.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('risk-center.index'),'label' => __('Risk Center'),'active' => request()->routeIs('risk-center.*'),'icon' => $iconRisk,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('risk-center.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Risk Center')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('risk-center.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconRisk),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'activity_logs.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('activity-logs.index'),'label' => __('Activity Logs'),'active' => request()->routeIs('activity-logs.*'),'icon' => $iconActivity,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('activity-logs.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Activity Logs')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('activity-logs.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconActivity),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'support.tickets.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('support-tickets.index'),'label' => __('Support Tickets'),'active' => request()->routeIs('support-tickets.*'),'icon' => $iconSupport,'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('support-tickets.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Support Tickets')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('support-tickets.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconSupport),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $attributes = $__attributesOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $component = $__componentOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__componentOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'hr.staff.view')): ?>
        <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('hr.index'),'label' => __('HR & Team'),'active' => request()->routeIs('hr.*'),'icon' => $iconHr]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hr.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('HR & Team')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hr.*')),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHr)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal19667d6231f8d2a7fc135a984252ca90 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19667d6231f8d2a7fc135a984252ca90 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'settings','label' => __('Settings'),'defaultOpen' => $settingsActive,'icon' => $iconSettings]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'settings','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Settings')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settingsActive),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconSettings)]); ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'rbac.manage')): ?>
            <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('access-control.permissions.index'),'label' => __('Access Control'),'active' => request()->routeIs('access-control.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('access-control.permissions.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access Control')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('access-control.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('users-roles.index'),'label' => __('Users & Roles'),'active' => request()->routeIs('users-roles.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('users-roles.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Users & Roles')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('users-roles.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'system_settings.update')): ?>
            <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('system-settings.edit'),'label' => __('System Settings'),'active' => request()->routeIs('system-settings.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('system-settings.edit')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System Settings')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('system-settings.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'api_credentials.view')): ?>
            <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('api-credentials.index'),'label' => __('API & Integrations'),'active' => request()->routeIs('api-credentials.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('api-credentials.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('API & Integrations')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('api-credentials.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'payments_gateway.view')): ?>
            <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('settings.payments-gateway.overview'),'label' => __('Payments Gateway'),'active' => request()->routeIs('settings.payments-gateway.*'),'nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('settings.payments-gateway.overview')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments Gateway')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('settings.payments-gateway.*')),'nested' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php $__currentLoopData = $paymentsGatewayMonitoringLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monitoringLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route($monitoringLink['route']),'label' => $monitoringLink['label'],'active' => request()->routeIs($monitoringLink['pattern']),'nested' => true,'class' => '!pl-11 text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route($monitoringLink['route'])),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($monitoringLink['label']),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs($monitoringLink['pattern'])),'nested' => true,'class' => '!pl-11 text-xs']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $attributes = $__attributesOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__attributesOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25b36b426f30fd196f9d947e60e48c56)): ?>
<?php $component = $__componentOriginal25b36b426f30fd196f9d947e60e48c56; ?>
<?php unset($__componentOriginal25b36b426f30fd196f9d947e60e48c56); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $attributes = $__attributesOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__attributesOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19667d6231f8d2a7fc135a984252ca90)): ?>
<?php $component = $__componentOriginal19667d6231f8d2a7fc135a984252ca90; ?>
<?php unset($__componentOriginal19667d6231f8d2a7fc135a984252ca90); ?>
<?php endif; ?>
</nav>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/partials/sidebar-nav.blade.php ENDPATH**/ ?>