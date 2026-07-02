<?php
    $initialSection = $initialSection ?? 'overview';
    $validSections = array_merge(['overview', 'product_app', 'api_reference', 'troubleshooting'], array_column($guide['checklist'], 'key'));
    if (! in_array($initialSection, $validSections, true)) {
        $initialSection = 'overview';
    }
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Integration Setup Guide'),'subheading' => __('Step-by-step API documentation for connecting your product systems to this dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Integration Setup Guide')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Step-by-step API documentation for connecting your product systems to this dashboard'))]); ?>
    <div
        x-data="{ section: <?php echo \Illuminate\Support\Js::from($initialSection)->toHtml() ?>, stubTab: 'license_middleware' }"
        class="space-y-6"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Control plane setup')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Integration Setup Guide')); ?></h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(__('Wire hosted product apps (Property, MFI, CRM, client domains), payments gateway, and CI/CD to this dashboard. All communication is server-to-server HTTP — no shared sessions.')); ?>

                </p>
            </div>
            <?php if(count($adminLinks) > 0): ?>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $adminLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($link['url']); ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <?php echo e($link['label']); ?> →
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 lg:grid-cols-4">
            <nav class="space-y-1 lg:col-span-1">
                <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Setup steps')); ?></p>
                <?php $__currentLoopData = [
                    'overview' => __('Overview & checklist'),
                    'product_app' => __('Product app (Cursor)'),
                    'dashboard' => __('1. Dashboard'),
                    'product' => __('2. Hosted product'),
                    'tenant' => __('3. Tenant'),
                    'license' => __('4. License API'),
                    'system_info' => __('5. System info API'),
                    'heartbeat' => __('6. Usage heartbeat'),
                    'payments' => __('7. Payments Gateway'),
                    'deployments' => __('8. CI webhooks'),
                    'verify' => __('9. Verify'),
                    'api_reference' => __('API reference'),
                    'troubleshooting' => __('Troubleshooting'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        @click="section = '<?php echo e($key); ?>'"
                        :class="section === '<?php echo e($key); ?>' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold transition"
                    ><?php echo e($label); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="lg:col-span-3 space-y-6">
                
                <div x-show="section === 'overview'" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('End-to-end checklist')); ?></h3>
                        <ol class="mt-4 space-y-3">
                            <?php $__currentLoopData = $guide['checklist']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white"><?php echo e($item['step']); ?></span>
                                    <div>
                                        <button type="button" @click="section = '<?php echo e($item['key']); ?>'" class="text-left text-sm font-semibold text-indigo-700 hover:underline dark:text-indigo-400"><?php echo e($item['label']); ?></button>
                                        <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"><?php echo e($item['description']); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ol>
                    </div>
                    <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Dashboard base URL'),'value' => $guide['app_url']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dashboard base URL')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['app_url'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('API base URL'),'value' => $guide['api_base']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('API base URL')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['api_base'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    <div class="rounded-2xl border border-violet-200/80 bg-violet-50/50 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                        <p class="text-sm font-semibold text-violet-900 dark:text-violet-200"><?php echo e(__('Implementing in another repo (MFI, Property, CRM)?')); ?></p>
                        <p class="mt-1 text-xs text-violet-800/90 dark:text-violet-300/90"><?php echo e(__('Open the Product app (Cursor) section for the implementation brief, stub file list, and copy-paste agent prompt.')); ?></p>
                        <button type="button" @click="section = 'product_app'" class="mt-2 text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Go to Product app guide')); ?> →</button>
                    </div>
                </div>

                
                <div x-show="section === 'product_app'" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-violet-200/80 bg-gradient-to-r from-violet-50/80 to-white p-4 dark:border-violet-900/50 dark:from-violet-950/30 dark:to-slate-900">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Product app implementation brief')); ?></h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('Use this when wiring a hosted product in Cursor or another IDE. Complete dashboard steps (tenant + credentials) first.')); ?></p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'Brief (in repo)','value' => $guide['product_implementation']['brief_doc'],'mono' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Brief (in repo)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['product_implementation']['brief_doc']),'mono' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'Stubs directory','value' => $guide['product_implementation']['stubs_dir'],'mono' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Stubs directory','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['product_implementation']['stubs_dir']),'mono' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'Cursor rule (copy to product)','value' => $guide['product_implementation']['cursor_rule'],'mono' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Cursor rule (copy to product)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['product_implementation']['cursor_rule']),'mono' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'Acceptance checks','value' => $guide['product_implementation']['acceptance_checks'],'mono' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Acceptance checks','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['product_implementation']['acceptance_checks']),'mono' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Stub file</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Required</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php $__currentLoopData = $guide['product_implementation']['stub_files']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs"><?php echo e($stub['file']); ?></td>
                                        <td class="px-4 py-2 text-xs"><?php echo e($stub['required'] ? 'Yes' : 'Optional'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Cursor agent prompt (paste in product project)')); ?></p>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'Prompt','value' => $guide['product_implementation']['cursor_prompt'],'mono' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Prompt','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['product_implementation']['cursor_prompt']),'mono' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    </div>
                </div>

                
                <div x-show="section === 'dashboard'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['dashboard']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500"><?php echo e(__('Variable')); ?></th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500"><?php echo e(__('Purpose')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php $__currentLoopData = $guide['env_dashboard']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 font-mono text-xs text-indigo-700 dark:text-indigo-400"><?php echo e($row['key']); ?></td>
                                        <td class="px-4 py-2 text-slate-600 dark:text-slate-400"><?php echo e($row['purpose']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-xs text-slate-500"><?php echo e(__('Run')); ?> <code class="rounded bg-slate-100 px-1 dark:bg-slate-800">php artisan migrate</code> <?php echo e(__('and start a queue worker')); ?> (<code class="rounded bg-slate-100 px-1 dark:bg-slate-800">php artisan queue:work</code> <?php echo e(__('or Horizon).')); ?></p>
                </div>

                
                <div x-show="section === 'product'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['product']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li><?php echo e(__('Go to Infrastructure → Hosted Projects and create a project.')); ?></li>
                        <li><?php echo e(__('Set domain and product_key (e.g. property, mfi, crm).')); ?></li>
                        <li><?php echo e(__('Copy the auto-generated API token — this becomes PRADY_PROJECT_API_TOKEN in each product installation.')); ?></li>
                    </ul>
                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'projects.view')): ?>
                        <a href="<?php echo e(route('hosted-projects.index')); ?>" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Hosted Projects')); ?> →</a>
                    <?php endif; ?>
                </div>

                
                <div x-show="section === 'tenant'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['tenant']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <?php $__currentLoopData = $guide['tenant_fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $desc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-700">
                                <code class="text-xs font-semibold text-indigo-700 dark:text-indigo-400"><?php echo e($field); ?></code>
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400"><?php echo e($desc); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'tenants.view')): ?>
                        <a href="<?php echo e(route('tenants.index')); ?>" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Tenants')); ?> →</a>
                    <?php endif; ?>
                </div>

                
                <div x-show="section === 'license'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['license']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="mt-4 space-y-3">
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'API endpoint','value' => $guide['api_base'].'/v1/license/check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'API endpoint','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['api_base'].'/v1/license/check')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Product .env block'),'value' => $guide['env_product_license'],'mono' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Product .env block')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['env_product_license']),'mono' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Sample request body')); ?></p>
                        <pre class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-300">{
  "tenant_key": "abc-properties",
  "product_key": "property",
  "domain": "abc.property.pradytecai.com"
}</pre>
                    </div>
                    <div class="mt-4">
                        <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Laravel stubs (copy to product app)')); ?></p>
                        <div class="mb-2 flex flex-wrap gap-1">
                            <?php $__currentLoopData = ['license_middleware' => __('Middleware'), 'license_config' => __('Config'), 'license_routes' => __('Routes')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" @click="stubTab = '<?php echo e($key); ?>'" :class="stubTab === '<?php echo e($key); ?>' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" class="rounded-lg px-3 py-1 text-xs font-semibold"><?php echo e($label); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__currentLoopData = ['license_middleware', 'license_config', 'license_routes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stubKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <pre x-show="stubTab === '<?php echo e($stubKey); ?>'" <?php if($stubKey !== 'license_middleware'): ?> x-cloak <?php endif; ?> class="max-h-80 overflow-auto rounded-xl bg-slate-900 p-3 text-xs text-slate-200"><?php echo e($guide['stubs'][$stubKey]); ?></pre>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                        <p class="font-semibold"><?php echo e(__('Access levels')); ?></p>
                        <p class="mt-1">full → normal · warning → banner · read_only → block mutations · blocked → deny access</p>
                    </div>
                </div>

                
                <div x-show="section === 'system_info'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['system_info']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="mt-4 space-y-3">
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Endpoint (on each product app)'),'value' => 'GET /api/system/info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Endpoint (on each product app)')),'value' => 'GET /api/system/info']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Auth header'),'value' => 'Authorization: Bearer {PRADY_DASHBOARD_API_TOKEN}']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Auth header')),'value' => 'Authorization: Bearer {PRADY_DASHBOARD_API_TOKEN}']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <p class="text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('See sample JSON, .env snippet, and Laravel stubs under Settings → API & Integrations → Tenant System APIs.')); ?></p>
                        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'api_credentials.view')): ?>
                            <a href="<?php echo e(route('api-credentials.index', ['tab' => 'tenant_system'])); ?>" class="inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Tenant System API contract')); ?> →</a>
                        <?php endif; ?>
                    </div>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('After exposing the endpoint, add a Tenant system API integration on the tenant Integrations tab with the same token as PRADY_DASHBOARD_API_TOKEN.')); ?></p>
                </div>

                
                <div x-show="section === 'heartbeat'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['heartbeat']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => 'API endpoint','value' => $guide['api_base'].'/v1/tenant/usage']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'API endpoint','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['api_base'].'/v1/tenant/usage')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    <pre class="mt-4 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-300">{
  "tenant_key": "&lt;tenant external_key UUID&gt;",
  "active_users": 18,
  "database_size_mb": 420.5,
  "storage_usage_mb": 1200,
  "reported_app_version": "2.4.1"
}</pre>
                    <p class="mt-2 text-xs text-amber-700 dark:text-amber-400"><?php echo e(__('Note: tenant_key in this API is the external_key UUID, not the human-readable tenant_key used in license checks.')); ?></p>
                </div>

                
                <div x-show="section === 'payments'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['payments']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="mt-4 space-y-3">
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Inbound webhook URL (configure on gateway)'),'value' => $guide['api_base'].'/v1/payments-gateway/webhooks']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Inbound webhook URL (configure on gateway)')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['api_base'].'/v1/payments-gateway/webhooks')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('PAYMENTS_GATEWAY_URL'),'value' => config('payment_gateway.base_url')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('PAYMENTS_GATEWAY_URL')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(config('payment_gateway.base_url'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    </div>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('Link tenants via Settings → Payments Gateway → Treasury Mapping. Tenant payment listener:')); ?> <code class="text-xs">https://{domain}/webhooks/payments-gateway/events</code></p>
                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'payments_gateway.view')): ?>
                        <a href="<?php echo e(route('settings.payments-gateway.overview')); ?>" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Payments Gateway')); ?> →</a>
                    <?php endif; ?>
                </div>

                
                <div x-show="section === 'deployments'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['deployments']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php if (isset($component)) { $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.copyable-field','data' => ['label' => __('Webhook URL pattern'),'value' => $guide['api_base'].'/v1/deployments/webhooks/{integration_id}']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.copyable-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Webhook URL pattern')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($guide['api_base'].'/v1/deployments/webhooks/{integration_id}')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $attributes = $__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__attributesOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3)): ?>
<?php $component = $__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3; ?>
<?php unset($__componentOriginalfd1c672fa6fd2ca92ea0be1607ac43b3); ?>
<?php endif; ?>
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400"><?php echo e(__('Create a Deployment Integration in the dashboard, then configure your CI provider with Bearer token or X-Hub-Signature-256 (GitHub style).')); ?></p>
                    <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'deployments.view')): ?>
                        <a href="<?php echo e(route('deployments.index')); ?>" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Deployments')); ?> →</a>
                    <?php endif; ?>
                </div>

                
                <div x-show="section === 'verify'" x-cloak>
                    <?php echo $__env->make('admin.integration-setup-guide.partials.section-header', ['section' => $guide['sections']['verify']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <li><?php echo e(__('Tenant command center → Integration readiness checklist')); ?></li>
                        <li><?php echo e(__('Integrations tab → Test connection / Pull system info')); ?></li>
                        <li><?php echo e(__('License Logs — confirm successful checks from product apps')); ?></li>
                        <li><?php echo e(__('curl license check from product server with real credentials')); ?></li>
                        <li><code class="text-xs">php artisan ops:health --json</code> <?php echo e(__('on dashboard server')); ?></li>
                    </ul>
                </div>

                
                <div x-show="section === 'api_reference'" x-cloak>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('API reference')); ?></h3>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">HTTP method</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">URL path</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Authentication</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Traffic direction</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php $__currentLoopData = $guide['endpoints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-3 py-2"><span class="rounded bg-indigo-100 px-1.5 py-0.5 font-mono text-xs font-bold text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300"><?php echo e($ep['method']); ?></span></td>
                                        <td class="px-3 py-2 font-mono text-xs break-all"><?php echo e($ep['path']); ?></td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-400"><?php echo e($ep['auth']); ?></td>
                                        <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-400"><?php echo e($ep['direction']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div x-show="section === 'troubleshooting'" x-cloak>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Common issues')); ?></h3>
                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $guide['troubleshooting']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-700">
                                <p class="text-sm font-semibold text-rose-700 dark:text-rose-400"><?php echo e($row['symptom']); ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Cause')); ?>: <?php echo e($row['cause']); ?></p>
                                <p class="mt-1 text-xs text-slate-700 dark:text-slate-300"><?php echo e(__('Fix')); ?>: <?php echo e($row['fix']); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/integration-setup-guide/index.blade.php ENDPATH**/ ?>