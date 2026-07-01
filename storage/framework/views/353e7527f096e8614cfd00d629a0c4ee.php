<?php
    $queryExceptPage = request()->except('page');
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Risk Center'),'subheading' => __('Operational risks detected from live data')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Risk Center')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Operational risks detected from live data'))]); ?>
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500"><?php echo e(__('Open risks')); ?></p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white"><?php echo e($counts['total']); ?></p>
        </div>
        <div class="rounded-xl border border-rose-200/80 bg-rose-50/50 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <p class="text-xs font-semibold uppercase tracking-widest text-rose-600"><?php echo e(__('Critical')); ?></p>
            <p class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-300"><?php echo e($counts['critical']); ?></p>
        </div>
        <div class="rounded-xl border border-amber-200/80 bg-amber-50/50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-700"><?php echo e(__('Warning')); ?></p>
            <p class="mt-1 text-2xl font-semibold text-amber-800 dark:text-amber-200"><?php echo e($counts['warning']); ?></p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500"><?php echo e(__('Acknowledged')); ?></p>
            <p class="mt-1 text-2xl font-semibold text-slate-600 dark:text-slate-300"><?php echo e($counts['acknowledged']); ?></p>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('risk-center.index')); ?>" class="mb-6 rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Keyword')); ?></label>
                <input name="q" value="<?php echo e($filters['q']); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Category')); ?></label>
                <select name="category" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if($filters['category'] === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Severity')); ?></label>
                <select name="severity" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = ['critical', 'warning', 'info']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sev); ?>" <?php if($filters['severity'] === $sev): echo 'selected'; endif; ?>><?php echo e(ucfirst($sev)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Acknowledged')); ?></label>
                <select name="acknowledged" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value="no" <?php if($filters['acknowledged'] === 'no'): echo 'selected'; endif; ?>><?php echo e(__('Hide acknowledged')); ?></option>
                    <option value="yes" <?php if($filters['acknowledged'] === 'yes'): echo 'selected'; endif; ?>><?php echo e(__('Acknowledged only')); ?></option>
                    <option value="" <?php if($filters['acknowledged'] === ''): echo 'selected'; endif; ?>><?php echo e(__('Show all')); ?></option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Tenant')); ?></label>
                <select name="tenant_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) $filters['tenant_id'] === (string) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Project')); ?></label>
                <select name="project_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) $filters['project_id'] === (string) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Server')); ?></label>
                <select name="server_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) $filters['server_id'] === (string) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Assigned staff')); ?></label>
                <select name="staff_profile_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                    <option value=""><?php echo e(__('All')); ?></option>
                    <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) $filters['staff_profile_id'] === (string) $id): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Due from')); ?></label>
                <input type="date" name="from" value="<?php echo e($filters['from']); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Due to')); ?></label>
                <input type="date" name="to" value="<?php echo e($filters['to']); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" />
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white"><?php echo e(__('Filter')); ?></button>
            <a href="<?php echo e(route('risk-center.index')); ?>" class="rounded-lg border px-4 py-2 text-xs font-semibold"><?php echo e(__('Reset')); ?></a>
        </div>
    </form>

    <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $categoryRisks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <section class="mb-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-widest text-slate-500"><?php echo e($categories[$category] ?? $category); ?></h2>
            <?php if (isset($component)) { $__componentOriginal7408c88f8f69ac708d2acdd799a27d40 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7408c88f8f69ac708d2acdd799a27d40 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-cards','data' => ['risks' => $categoryRisks,'title' => null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-cards'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['risks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categoryRisks),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(null)]); ?>
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
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700"><?php echo e(__('No operational risks match your filters.')); ?></p>
    <?php endif; ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/risk-center/index.blade.php ENDPATH**/ ?>