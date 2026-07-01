<?php
    $meta = is_array($server->provisioning_meta) ? $server->provisioning_meta : [];
    $showVal = fn ($value) => filled($value) ? $value : __('Not configured');

    $tabs = [
        'overview' => __('Overview'),
        'health' => __('Health'),
        'deployments' => __('Hosted deployments'),
        'notices' => __('Notices'),
        'billing' => __('Billing'),
        'activity' => __('Activity'),
        'advanced' => __('Advanced'),
    ];

    $statusVariant = match ($server->status) {
        'online' => 'success',
        'warning' => 'warning',
        'offline' => 'danger',
        default => 'neutral',
    };
    $renewalVariant = match ($server->renewalRisk()) {
        'overdue' => 'danger',
        'soon' => 'warning',
        default => 'neutral',
    };

    $readiness = [
        ['label' => __('Public IP provided'), 'done' => filled($server->ip_address)],
        ['label' => __('Hostname provided'), 'done' => filled($server->hostname())],
        ['label' => __('Telemetry mode selected'), 'done' => filled($server->telemetry_mode)],
    ];
    if ($server->telemetry_mode === 'whm') {
        $readiness[] = ['label' => __('WHM endpoint provided'), 'done' => filled($meta['api_endpoint'] ?? null)];
        $readiness[] = ['label' => __('API token provided'), 'done' => $server->hasWhmCredentials()];
    }
    $readiness[] = ['label' => __('Renewal date provided'), 'done' => $server->renewal_expires_at !== null];
    $readiness[] = ['label' => __('Monthly cost provided'), 'done' => filled($server->monthly_cost)];
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $server->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($server->name)]); ?>
    <?php if (isset($component)) { $__componentOriginal7408c88f8f69ac708d2acdd799a27d40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7408c88f8f69ac708d2acdd799a27d40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-cards','data' => ['risks' => $operationalRisks,'class' => 'mb-6','compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-cards'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['risks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($operationalRisks),'class' => 'mb-6','compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7408c88f8f69ac708d2acdd799a27d40)): ?>
<?php $attributes = $__attributesOriginal7408c88f8f69ac708d2acdd799a27d40; ?>
<?php unset($__attributesOriginal7408c88f8f69ac708d2acdd799a27d40); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7408c88f8f69ac708d2acdd799a27d40)): ?>
<?php $component = $__componentOriginal7408c88f8f69ac708d2acdd799a27d40; ?>
<?php unset($__componentOriginal7408c88f8f69ac708d2acdd799a27d40); ?>
<?php endif; ?>

    <div x-data="{ activeTab: window.location.hash === '#notices' ? 'notices' : 'overview' }" class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Server')); ?></p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e($showVal($server->provider)); ?>

                    <?php if($server->hostname()): ?>
                        · <span class="font-mono"><?php echo e($server->hostname()); ?></span>
                    <?php endif; ?>
                    <?php if($server->ip_address): ?>
                        · <span class="font-mono"><?php echo e($server->ip_address); ?></span>
                    <?php endif; ?>
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant)]); ?><?php echo e(ucfirst($server->status)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    <span class="inline-flex items-center rounded-full bg-slate-500/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 ring-1 ring-slate-500/20 dark:text-slate-300">
                        <?php echo e($server->telemetryModeLabel()); ?>

                    </span>
                    <?php if($server->telemetry_mode === 'whm' && ! $server->hasWhmCredentials()): ?>
                        <span class="inline-flex items-center rounded-full bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-amber-800 ring-1 ring-amber-500/20 dark:text-amber-200">
                            <?php echo e(__('Add WHM API token to enable live metrics')); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if($server->telemetry_mode !== 'manual'): ?>
                    <form method="post" action="<?php echo e(route('servers.sync-telemetry', $server)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-3 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-500/15 dark:text-cyan-200">
                            <?php echo e(__('Sync now')); ?>

                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('servers.edit', $server)); ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><?php echo e(__('Edit')); ?></a>
                <form method="post" action="<?php echo e(route('servers.destroy', $server)); ?>" onsubmit="return confirm(<?php echo json_encode(__('Delete :name from the fleet?', ['name' => $server->name]), 512) ?>);">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('delete'); ?>
                    <button type="submit" class="inline-flex items-center rounded-xl border border-rose-200/80 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        <?php echo e(__('Delete')); ?>

                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-slate-100/80 p-1 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="flex min-w-max gap-1">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" @click="activeTab = '<?php echo e($id); ?>'" :class="activeTab === '<?php echo e($id); ?>' ? 'bg-white text-indigo-600 shadow dark:bg-slate-800 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400'" class="rounded-lg px-3 py-2 text-[11px] font-semibold whitespace-nowrap transition"><?php echo e($label); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div x-show="activeTab === 'overview'" class="space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Summary')); ?></h3>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    <div><dt class="text-slate-500"><?php echo e(__('Server name')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($showVal($server->name)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Provider')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($showVal($server->provider)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Public IP')); ?></dt><dd class="mt-0.5 font-mono font-medium"><?php echo e($showVal($server->ip_address)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Hostname')); ?></dt><dd class="mt-0.5 font-mono font-medium"><?php echo e($showVal($server->hostname())); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Environment')); ?></dt><dd class="mt-0.5 font-medium capitalize"><?php echo e($showVal($meta['environment'] ?? null)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd class="mt-0.5 font-medium capitalize"><?php echo e($showVal($server->status)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Telemetry mode')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($server->telemetryModeLabel()); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Monthly cost')); ?></dt><dd class="mt-0.5 font-medium tabular-nums"><?php echo e(filled($server->monthly_cost) ? $server->currency.' '.number_format((float) $server->monthly_cost, 2) : __('Not configured')); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Renewal date')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($server->renewal_expires_at?->format('M j, Y') ?? __('Not configured')); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('SSL status')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($showVal($server->ssl_status)); ?></dd></div>
                    <div><dt class="text-slate-500"><?php echo e(__('Backup status')); ?></dt><dd class="mt-0.5 font-medium"><?php echo e($showVal($server->backup_status)); ?></dd></div>
                </dl>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Readiness checklist')); ?></h3>
                    <ul class="mt-4 space-y-2">
                        <?php $__currentLoopData = $readiness; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-2 text-sm">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[10px] <?php echo e($item['done'] ? 'bg-emerald-500/20 text-emerald-600' : 'bg-slate-200 text-slate-400 dark:bg-slate-700'); ?>">
                                    <?php if($item['done']): ?> <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'check','class' => 'text-emerald-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'text-emerald-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?> <?php endif; ?>
                                </span>
                                <span class="<?php echo e($item['done'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-500'); ?>"><?php echo e($item['label']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Sync & capacity')); ?></h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Last sync')); ?></dt><dd class="font-medium"><?php echo e($server->last_synced_at?->diffForHumans() ?? __('Never')); ?></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Disk usage')); ?></dt><dd class="font-medium"><?php echo e($server->disk_usage_percent !== null ? $server->disk_usage_percent.'%' : __('Not configured')); ?></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('RAM usage')); ?></dt><dd class="font-medium"><?php echo e($server->displayRamPercent() !== null ? $server->displayRamPercent().'%' : __('Not configured')); ?></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Load avg')); ?></dt><dd class="font-medium"><?php echo e($server->displayLoad() ?? __('Not configured')); ?></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500"><?php echo e(__('Accounts')); ?></dt><dd class="font-medium"><?php echo e($server->account_count ?? __('Not configured')); ?></dd></div>
                    </dl>
                    <?php if($server->sync_message): ?>
                        <p class="mt-3 text-xs text-slate-500"><?php echo e($server->sync_message); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal758a4d06f179e1d63015d8fd45f690dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.assigned-staff','data' => ['assignments' => $staffAssignments,'title' => __('Responsible staff')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.assigned-staff'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['assignments' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($staffAssignments),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Responsible staff'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $attributes = $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $component = $__componentOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>
        </div>

        
        <div x-show="activeTab === 'health'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs text-slate-500"><?php echo e(__('Last checked')); ?>: <?php echo e($server->meta('last_health_checked_at') ? \Illuminate\Support\Carbon::parse($server->meta('last_health_checked_at'))->diffForHumans() : __('Never')); ?></p>
            <ul class="mt-4 space-y-2 text-sm">
                <?php
                    $checkLabels = [
                        'port_443' => __('HTTPS (443)'),
                        'port_80' => __('HTTP (80)'),
                        'port_22' => __('SSH (22)'),
                        'port_2087' => __('WHM (2087)'),
                        'dns_resolves' => __('DNS resolves'),
                        'dns_matches_ip' => __('DNS matches IP'),
                        'ssl_reachable' => __('SSL endpoint'),
                        'whm_api' => __('WHM API'),
                    ];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $checkLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if(array_key_exists($key, $healthChecks)): ?>
                        <li class="flex items-center justify-between gap-4 rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/50">
                            <span><?php echo e($label); ?></span>
                            <span class="font-semibold <?php echo e(($healthChecks[$key] ?? false) ? 'text-emerald-600' : 'text-rose-600'); ?>">
                                <?php echo e(($healthChecks[$key] ?? false) ? __('Pass') : __('Fail')); ?>

                            </span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
                <?php if($healthChecks === []): ?>
                    <li class="text-slate-500"><?php echo e(__('Run sync to populate health checks (requires IP or hostname).')); ?></li>
                <?php endif; ?>
            </ul>
            <?php if($server->latestHealthLog): ?>
                <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500 dark:border-slate-800">
                    <?php echo e(__('Latest metrics log')); ?>: CPU <?php echo e($server->latestHealthLog->cpu_percent ?? '—'); ?>% · RAM <?php echo e($server->latestHealthLog->ram_percent ?? '—'); ?>% · <?php echo e($server->latestHealthLog->checked_at->diffForHumans()); ?>

                </p>
            <?php endif; ?>
        </div>

        
        <div x-show="activeTab === 'deployments'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Tenant project deployments')); ?></h3>
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $server->tenantProjectDeployments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deployment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $sub = $deployment->subscription; ?>
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                            <div>
                                <a href="<?php echo e(route('tenants.show', $sub?->tenant)); ?>" class="font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($sub?->tenant?->company_name ?? __('Unknown tenant')); ?></a>
                                <span class="text-slate-500">· <?php echo e($sub?->project?->name); ?></span>
                                <?php if($deployment->domain): ?>
                                    <p class="text-xs text-slate-500 font-mono"><?php echo e($deployment->domain); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <p><?php echo e(__('Version')); ?>: <?php echo e($sub?->versionTracking?->current_version ?? __('Unknown')); ?></p>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-4 text-slate-500"><?php echo e(__('No tenant project deployments on this server.')); ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold"><?php echo e(__('Projects & domains')); ?></h3>
                <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $server->projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="py-2 text-sm">
                            <a href="<?php echo e(route('hosted-projects.show', $project)); ?>" class="font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($project->name); ?></a>
                            <span class="text-slate-500">· <?php echo e($project->domain); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-2 text-slate-500"><?php echo e(__('No projects linked.')); ?></li>
                    <?php endif; ?>
                </ul>
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $server->hosted_domains ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="py-2 font-mono text-sm"><?php echo e($domain); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-2 text-slate-500"><?php echo e(__('No hosted domains recorded.')); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        
        <div x-show="activeTab === 'notices'" x-cloak id="notices" class="space-y-6">
            <?php echo $__env->make('admin.servers.partials.show.notice-form', ['server' => $server], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <ul class="divide-y divide-slate-100 rounded-2xl border border-slate-200/80 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
                <?php $__empty_1 = true; $__currentLoopData = $server->providerNotices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="px-4 py-4 text-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <span class="font-semibold text-slate-900 dark:text-white"><?php echo e($notice->title); ?></span>
                                <span class="ml-2 text-[10px] uppercase text-slate-400"><?php echo e($notice->notice_type); ?> · <?php echo e($notice->severity); ?></span>
                            </div>
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $notice->status === 'open' ? 'warning' : 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notice->status === 'open' ? 'warning' : 'neutral')]); ?><?php echo e(ucfirst($notice->status)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                        </div>
                        <?php if($notice->body): ?>
                            <p class="mt-2 text-slate-600 dark:text-slate-300"><?php echo e($notice->body); ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($notice->notice_date->format('M j, Y')); ?></p>
                        <form method="post" action="<?php echo e(route('servers.notices.destroy', [$server, $notice])); ?>" class="mt-2 inline" onsubmit="return confirm(<?php echo json_encode(__('Remove notice?'), 15, 512) ?>)">
                            <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                            <button type="submit" class="text-xs text-rose-600 hover:underline"><?php echo e(__('Remove')); ?></button>
                        </form>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="px-4 py-6 text-sm text-slate-500"><?php echo e(__('No provider notices yet.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        
        <div x-show="activeTab === 'billing'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Monthly cost')); ?></dt><dd class="font-medium"><?php echo e(filled($server->monthly_cost) ? $server->currency.' '.number_format((float) $server->monthly_cost, 2) : __('Not configured')); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Renewal / expiry')); ?></dt><dd class="font-medium">
                    <?php echo e($server->renewal_expires_at?->toFormattedDateString() ?? __('Not configured')); ?>

                    <?php if($server->renewal_expires_at): ?>
                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $renewalVariant,'class' => 'ml-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($renewalVariant),'class' => 'ml-2']); ?><?php echo e(ucfirst($server->renewalRisk())); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    <?php endif; ?>
                </dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Billing status')); ?></dt><dd class="font-medium"><?php echo e($showVal($server->billing_status)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Billing cycle')); ?></dt><dd class="font-medium"><?php echo e($showVal($meta['billing_cycle'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Provider invoice ref')); ?></dt><dd class="font-medium"><?php echo e($showVal($meta['provider_invoice_ref'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Billing notes')); ?></dt><dd class="whitespace-pre-wrap"><?php echo e($showVal($meta['billing_notes'] ?? null)); ?></dd></div>
            </dl>
        </div>

        
        <div x-show="activeTab === 'activity'" x-cloak>
            <?php if (isset($component)) { $__componentOriginalc535bf0441c81dd81939b35e9ab2587f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc535bf0441c81dd81939b35e9ab2587f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.activity-feed','data' => ['logs' => $activityLogs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.activity-feed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activityLogs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc535bf0441c81dd81939b35e9ab2587f)): ?>
<?php $attributes = $__attributesOriginalc535bf0441c81dd81939b35e9ab2587f; ?>
<?php unset($__attributesOriginalc535bf0441c81dd81939b35e9ab2587f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc535bf0441c81dd81939b35e9ab2587f)): ?>
<?php $component = $__componentOriginalc535bf0441c81dd81939b35e9ab2587f; ?>
<?php unset($__componentOriginalc535bf0441c81dd81939b35e9ab2587f); ?>
<?php endif; ?>
        </div>

        
        <div x-show="activeTab === 'advanced'" x-cloak class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500"><?php echo e(__('Private IP')); ?></dt><dd class="mt-0.5 font-mono"><?php echo e($showVal($meta['private_ip'] ?? null)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Region / zone')); ?></dt><dd class="mt-0.5"><?php echo e($showVal($meta['region'] ?? null)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Cloud instance ID')); ?></dt><dd class="mt-0.5 font-mono"><?php echo e($showVal($meta['cloud_instance_id'] ?? null)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('OS')); ?></dt><dd class="mt-0.5"><?php echo e($showVal($meta['operating_system'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('API endpoint')); ?></dt><dd class="mt-0.5 font-mono text-xs"><?php echo e($showVal($meta['api_endpoint'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('WHM reference')); ?></dt><dd class="mt-0.5 whitespace-pre-wrap"><?php echo e($showVal($server->whm_cpanel_reference)); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('SSH')); ?></dt><dd class="font-mono"><?php echo e(($meta['ssh_username'] ?? '—')); ?>{{ $server->ip_address ?? $server->hostname() ?? '—' }}:<?php echo e($meta['ssh_port'] ?? 22); ?></dd></div>
                <div><dt class="text-slate-500"><?php echo e(__('Firewall')); ?></dt><dd><?php echo e($showVal($meta['firewall_status'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Access restrictions')); ?></dt><dd class="whitespace-pre-wrap"><?php echo e($showVal($meta['access_restrictions'] ?? null)); ?></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500"><?php echo e(__('Notes')); ?></dt><dd class="mt-1 whitespace-pre-wrap"><?php echo e($showVal($server->notes)); ?></dd></div>
            </dl>
            <p class="mt-4 text-xs text-slate-500"><?php echo e(__('API tokens are never displayed after saving.')); ?></p>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $attributes = $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__attributesOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7)): ?>
<?php $component = $__componentOriginal895f6ef515592ffd4805667c75b9d7a7; ?>
<?php unset($__componentOriginal895f6ef515592ffd4805667c75b9d7a7); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/servers/show.blade.php ENDPATH**/ ?>