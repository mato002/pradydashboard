<?php
    $health = $monitoringSnapshot['health'] ?? [];
    $redis = $monitoringSnapshot['redis'] ?? [];
    $horizon = $monitoringSnapshot['horizon'] ?? [];
    $failedCount = (int) ($health['failed_jobs_count'] ?? 0);
    $pendingCount = (int) ($health['total_pending'] ?? 0);
    $redisOk = (bool) ($redis['available'] ?? false);
?>

<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Monitoring & queues')); ?></h3>
            <p class="text-xs text-slate-500"><?php echo e(__('Redis health, queue backlog, and observability shortcuts')); ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('monitoring.index')); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                <?php echo e(__('Observability center')); ?>

            </a>
            <a href="<?php echo e(route('monitoring.queues')); ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-500">
                <?php echo e(__('Redis & Queues')); ?>

            </a>
        </div>
    </div>

    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Redis')); ?></p>
            <p class="mt-1 text-xl font-semibold <?php echo e($redisOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>">
                <?php echo e($redis['label'] ?? __('Unknown')); ?>

            </p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Horizon')); ?></p>
            <p class="mt-1 text-xl font-semibold text-slate-900 dark:text-white"><?php echo e($horizon['label'] ?? __('Unknown')); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Pending jobs')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-slate-900 dark:text-white"><?php echo e(number_format($pendingCount)); ?></p>
            <?php if($health['queues_clear'] ?? false): ?>
                <p class="mt-0.5 text-[11px] text-emerald-600 dark:text-emerald-400"><?php echo e(__('Queues are clear.')); ?></p>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Failed jobs')); ?></p>
            <p class="mt-1 text-xl font-semibold tabular-nums <?php echo e($failedCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'); ?>"><?php echo e(number_format($failedCount)); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500"><?php echo e(__('Busiest queue')); ?></p>
            <?php if($health['busiest_queue'] ?? null): ?>
                <p class="mt-1 font-mono text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($health['busiest_queue']['queue']); ?></p>
                <p class="text-[11px] text-slate-500"><?php echo e(number_format($health['busiest_queue']['pending'])); ?> <?php echo e(__('pending')); ?></p>
            <?php else: ?>
                <p class="mt-1 text-sm font-medium text-emerald-600 dark:text-emerald-400"><?php echo e(__('All clear')); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'server_health.view')): ?>
            <a href="<?php echo e(route('server-health.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Server health')); ?></a>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'activity_logs.view')): ?>
            <a href="<?php echo e(route('activity-logs.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Activity logs')); ?></a>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('permission', 'risk_center.view')): ?>
            <a href="<?php echo e(route('risk-center.index')); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Risk center')); ?></a>
        <?php endif; ?>
        <?php if($monitoringSnapshot['horizon_enabled'] ?? false): ?>
            <a href="<?php echo e(url('/'.trim((string) ($monitoringSnapshot['horizon_path'] ?? 'horizon'), '/'))); ?>" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e(__('Open Horizon')); ?></a>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/dashboard/partials/monitoring-ops.blade.php ENDPATH**/ ?>