<?php if($recentActivity->isNotEmpty()): ?>
    <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Recent activity')); ?></h3>
                <p class="text-xs text-slate-500"><?php echo e(__('Last :n operational changes', ['n' => $recentActivity->count()])); ?></p>
            </div>
            <a href="<?php echo e(route('activity-logs.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('View all')); ?></a>
        </div>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex flex-wrap items-start justify-between gap-2 px-5 py-3 text-sm">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-white"><?php echo e($log->description); ?></p>
                        <p class="text-xs text-slate-500">
                            <?php echo e($log->actorDisplayName()); ?>

                            · <?php echo e($log->categoryLabel()); ?>

                            <?php if($log->tenant): ?>
                                · <?php echo e($log->tenant->company_name); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                    <time class="text-xs text-slate-500"><?php echo e($log->created_at?->diffForHumans()); ?></time>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/dashboard/partials/recent-activity.blade.php ENDPATH**/ ?>