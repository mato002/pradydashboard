<?php
    $tabUrl = fn (string $t) => route('tenants.show', $tenant).'?tab='.urlencode($t);
?>

<div class="tenant-workspace-tabs sticky top-[4.25rem] z-10 -mx-4 min-w-0 max-w-full border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/90 sm:-mx-6 lg:-mx-8">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="lg:hidden">
            <label for="tenant-tab-mobile" class="sr-only"><?php echo e(__('Section')); ?></label>
            <select
                id="tenant-tab-mobile"
                class="my-2 w-full rounded-xl border border-slate-200/80 bg-white py-2.5 pl-3 pr-8 text-sm font-medium text-slate-800 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                :value="activeTab"
                @change="navigate($event.target.value)"
            >
                <?php $__currentLoopData = $workspaceTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if($tab === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="tenant-tab-scroll-wrap relative hidden min-w-0 lg:block">
            <nav
                class="tenant-tab-scroll flex min-w-0 gap-1 overflow-x-auto overscroll-x-contain py-2"
                aria-label="<?php echo e(__('Tenant sections')); ?>"
            >
                <?php $__currentLoopData = $workspaceTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e($tabUrl($key)); ?>"
                        data-tenant-tab="<?php echo e($key); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'shrink-0 whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-medium transition',
                            'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' => $tab === $key,
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => $tab !== $key,
                        ]); ?>"
                        :class="activeTab === <?php echo \Illuminate\Support\Js::from($key)->toHtml() ?> ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'"
                        aria-current="<?php echo e($tab === $key ? 'page' : 'false'); ?>"
                    ><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/tenants/partials/workspace/tabs.blade.php ENDPATH**/ ?>