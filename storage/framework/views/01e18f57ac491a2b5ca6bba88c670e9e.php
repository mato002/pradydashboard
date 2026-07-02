<?php
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    $textareaClass = $selectClass.' min-h-[80px]';
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Provision tenant'),'subheading' => __('Add a new organization — only the essentials')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Provision tenant')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add a new organization — only the essentials'))]); ?>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400"><?php echo e(__('Tenant management')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Provision tenant')); ?></h2>
                <p class="mt-1 max-w-xl text-sm text-slate-500 dark:text-slate-400">
                    <?php echo e(__('Company, product, and plan — everything else can be added later from the tenant command center.')); ?>

                </p>
            </div>
            <a href="<?php echo e(route('tenants.index')); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                <?php echo e(__('Back to tenants')); ?>

            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="rounded-xl border border-rose-200/80 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-100">
                <p class="font-semibold"><?php echo e(__('Please fix the following:')); ?></p>
                <ul class="mt-2 list-inside list-disc text-xs">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($message); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo e(route('tenants.store')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                <div class="space-y-8 p-6 sm:p-8">
                    <section class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Who is this tenant?')); ?></h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Primary organization and contact')); ?></p>
                        </div>
                        <?php echo $__env->make('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'organization',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </section>

                    <section class="space-y-4 border-t border-slate-200/80 pt-8 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Which product?')); ?></h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Hosted app and domain for license checks')); ?></p>
                        </div>
                        <?php echo $__env->make('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'product',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </section>

                    <section class="space-y-4 border-t border-slate-200/80 pt-8 dark:border-slate-800">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Billing')); ?></h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"><?php echo e(__('Pick a plan — currency, cycle, and trial status use sensible defaults')); ?></p>
                        </div>
                        <?php echo $__env->make('admin.tenants._form', [
                            'tenant' => $tenant,
                            'preselectedProjectId' => $preselectedProjectId ?? null,
                            'projects' => $projects,
                            'servers' => $servers,
                            'plans' => $plans ?? collect(),
                            'section' => 'billing',
                            'compact' => true,
                            'selectClass' => $selectClass,
                            'textareaClass' => $textareaClass,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </section>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-950/50">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <?php echo e(__('KRA PIN, server, cPanel, and other ops details → tenant profile after save.')); ?>

                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="<?php echo e(route('tenants.index')); ?>" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200/60 dark:text-slate-300 dark:hover:bg-slate-800">
                            <?php echo e(__('Cancel')); ?>

                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <?php echo e(__('Provision tenant')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            document.getElementById('saas_plan_id')?.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (!opt?.dataset?.name) return;
                const planInput = document.getElementById('subscription_plan');
                const amountInput = document.getElementById('subscription_amount');
                if (planInput) planInput.value = opt.dataset.name;
                if (amountInput && opt.dataset.amount) amountInput.value = opt.dataset.amount;
            });
        </script>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/tenants/create.blade.php ENDPATH**/ ?>