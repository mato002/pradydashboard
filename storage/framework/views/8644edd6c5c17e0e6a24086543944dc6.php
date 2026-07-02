<?php
    use App\Support\Rbac\Rbac;

    $infraActive = request()->routeIs('servers.*', 'hosted-projects.*', 'projects.*', 'products.*', 'ssl-domains.*', 'backups.*', 'server-health.*');
    $tenantActive = request()->routeIs('tenants.*', 'subscriptions.*', 'license-logs.*', 'access-controls.*');
    $financialActive = request()->routeIs('invoices.*', 'payments.*') && ! request()->routeIs('settings.payments-gateway.*');
    $opsActive = request()->routeIs('deployments.*', 'monitoring.*', 'risk-center.*', 'activity-logs.*', 'support-tickets.*');
    $settingsActive = request()->routeIs('access-control.*', 'users-roles.*', 'system-settings.*', 'api-credentials.*', 'settings.payments-gateway.*', 'integration-setup-guide.*');
    $guideActive = request()->routeIs('integration-setup-guide.*');
?>

<nav class="flex flex-1 flex-col gap-1 px-2 text-[13px] font-medium">
    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'dashboard.view')): ?>
        <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('dashboard'),'label' => __('Overview'),'active' => request()->routeIs('dashboard'),'iconName' => 'gauge-high']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('dashboard')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Overview')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard')),'icon-name' => 'gauge-high']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'infrastructure','label' => __('Infrastructure'),'defaultOpen' => $infraActive,'iconName' => 'network-wired']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'infrastructure','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Infrastructure')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($infraActive),'icon-name' => 'network-wired']); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'servers.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('servers.index'),'label' => __('Servers'),'active' => request()->routeIs('servers.*'),'iconName' => 'server','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('servers.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Servers')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('servers.*')),'icon-name' => 'server','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('hosted-projects.index'),'label' => __('Hosted Projects'),'active' => request()->routeIs('hosted-projects.*', 'projects.*'),'iconName' => 'layer-group','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hosted-projects.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Hosted Projects')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hosted-projects.*', 'projects.*')),'icon-name' => 'layer-group','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('products.index'),'label' => __('Products'),'active' => request()->routeIs('products.*'),'iconName' => 'box','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('products.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Products')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('products.*')),'icon-name' => 'box','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('ssl-domains.index'),'label' => __('SSL & Domains'),'active' => request()->routeIs('ssl-domains.*'),'iconName' => 'shield-halved','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ssl-domains.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('SSL & Domains')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('ssl-domains.*')),'icon-name' => 'shield-halved','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('backups.index'),'label' => __('Backups'),'active' => request()->routeIs('backups.*'),'iconName' => 'database','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('backups.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Backups')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('backups.*')),'icon-name' => 'database','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('server-health.index'),'label' => __('Server Health'),'active' => request()->routeIs('server-health.*'),'iconName' => 'heart-pulse','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('server-health.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Server Health')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('server-health.*')),'icon-name' => 'heart-pulse','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'tenants','label' => __('Tenants'),'defaultOpen' => $tenantActive,'iconName' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'tenants','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tenants')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tenantActive),'icon-name' => 'users']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('subscriptions.index'),'label' => __('Subscriptions'),'active' => request()->routeIs('subscriptions.*'),'iconName' => 'credit-card','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('subscriptions.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Subscriptions')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('subscriptions.*')),'icon-name' => 'credit-card','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('license-logs.index'),'label' => __('License Logs'),'active' => request()->routeIs('license-logs.*'),'iconName' => 'key','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('license-logs.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('License Logs')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('license-logs.*')),'icon-name' => 'key','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('access-controls.index'),'label' => __('Access Controls'),'active' => request()->routeIs('access-controls.*'),'iconName' => 'lock','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('access-controls.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Access Controls')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('access-controls.*')),'icon-name' => 'lock','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'financials','label' => __('Financials'),'defaultOpen' => $financialActive,'iconName' => 'file-invoice-dollar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'financials','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Financials')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($financialActive),'icon-name' => 'file-invoice-dollar']); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'invoices.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('invoices.index'),'label' => __('Invoices'),'active' => request()->routeIs('invoices.*'),'iconName' => 'file-invoice','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Invoices')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('invoices.*')),'icon-name' => 'file-invoice','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('payments.index'),'label' => __('Payments'),'active' => request()->routeIs('payments.*'),'iconName' => 'money-bill-wave','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('payments.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Payments')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('payments.*')),'icon-name' => 'money-bill-wave','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'operations','label' => __('Operations'),'defaultOpen' => $opsActive,'iconName' => 'chart-column']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'operations','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Operations')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opsActive),'icon-name' => 'chart-column']); ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'deployments.view')): ?>
                <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('deployments.index'),'label' => __('Deployments'),'active' => request()->routeIs('deployments.*'),'iconName' => 'rocket','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('deployments.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Deployments')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('deployments.*')),'icon-name' => 'rocket','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('monitoring.index'),'label' => __('Monitoring'),'active' => request()->routeIs('monitoring.index'),'iconName' => 'chart-line','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitoring')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('monitoring.index')),'icon-name' => 'chart-line','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('monitoring.queues'),'label' => __('Redis & Queues'),'active' => request()->routeIs('monitoring.queues'),'iconName' => 'list-ul','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitoring.queues')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Redis & Queues')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('monitoring.queues')),'icon-name' => 'list-ul','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('risk-center.index'),'label' => __('Risk Center'),'active' => request()->routeIs('risk-center.*'),'iconName' => 'triangle-exclamation','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('risk-center.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Risk Center')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('risk-center.*')),'icon-name' => 'triangle-exclamation','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('activity-logs.index'),'label' => __('Activity Logs'),'active' => request()->routeIs('activity-logs.*'),'iconName' => 'clock','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('activity-logs.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Activity Logs')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('activity-logs.*')),'icon-name' => 'clock','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('support-tickets.index'),'label' => __('Support Tickets'),'active' => request()->routeIs('support-tickets.*'),'iconName' => 'life-ring','nested' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('support-tickets.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Support Tickets')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('support-tickets.*')),'icon-name' => 'life-ring','nested' => true]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('hr.index'),'label' => __('HR & Team'),'active' => request()->routeIs('hr.*'),'iconName' => 'user-tie']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hr.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('HR & Team')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hr.*')),'icon-name' => 'user-tie']); ?>
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

    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'dashboard.view')): ?>
        <?php if (isset($component)) { $__componentOriginal25b36b426f30fd196f9d947e60e48c56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25b36b426f30fd196f9d947e60e48c56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-link','data' => ['href' => route('integration-setup-guide.index'),'label' => __('Integration Setup Guide'),'active' => $guideActive,'iconName' => 'book']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('integration-setup-guide.index')),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Integration Setup Guide')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guideActive),'icon-name' => 'book']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar-group','data' => ['id' => 'settings','label' => __('Settings'),'defaultOpen' => $settingsActive,'iconName' => 'gear']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'settings','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Settings')),'default-open' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settingsActive),'icon-name' => 'gear']); ?>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/partials/sidebar-nav.blade.php ENDPATH**/ ?>