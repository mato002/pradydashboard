<?php
    $overdueFollowUps = $supportSummary['overdue_follow_ups'] ?? 0;
?>

<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Support & communications')); ?></h3>
            <p class="text-xs text-slate-500"><?php echo e(__('Operational queue from live database records')); ?></p>
        </div>
        <a href="<?php echo e(route('support-tickets.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open support center')); ?></a>
    </div>
    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Open tickets')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums"><?php echo e($supportSummary['open_tickets']); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Urgent')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-rose-600"><?php echo e($supportSummary['urgent_tickets']); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Overdue tickets')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-amber-600"><?php echo e($supportSummary['overdue_tickets']); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Overdue follow-ups')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-rose-600"><?php echo e($overdueFollowUps); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Tenants with open issues')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums"><?php echo e($supportSummary['tenants_with_open_issues']); ?></p>
        </div>
    </div>
    <?php if(($supportSummary['recent_communications'] ?? collect())->isNotEmpty()): ?>
        <div class="border-t border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
            <p class="mb-2 text-xs font-semibold uppercase text-slate-500"><?php echo e(__('Recent communications')); ?></p>
            <ul class="space-y-2 text-sm">
                <?php $__currentLoopData = $supportSummary['recent_communications']->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex flex-wrap justify-between gap-2">
                        <span>
                            <a href="<?php echo e(route('tenants.show', ['tenant' => $comm->tenant_id, 'tab' => 'communications'])); ?>" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                <?php echo e($comm->tenant?->company_name ?? __('Tenant')); ?>

                            </a>
                            — <?php echo e(\Illuminate\Support\Str::limit($comm->message, 60)); ?>

                        </span>
                        <span class="text-xs text-slate-500"><?php echo e($comm->communication_date->diffForHumans()); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/dashboard/partials/support-ops.blade.php ENDPATH**/ ?>