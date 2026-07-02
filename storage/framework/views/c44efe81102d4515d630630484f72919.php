<?php
    $trendMax = max(collect($invoiceTrend)->max(fn ($p) => max($p['issued'], $p['paid'])) ?? 0, 1);
    $revenueMax = max(collect($revenueSeries)->max('value') ?? 0, 1);
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => __('Financial Operations'),'subheading' => __('Billing command center')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Financial Operations')),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Billing command center'))]); ?>
    <div x-data="{ toast: <?php echo \Illuminate\Support\Js::from(session('status'))->toHtml() ?> }" x-init="if (toast) setTimeout(() => toast = null, 5000)" class="space-y-5">
        <div x-show="toast" x-transition class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-emerald-500/30 bg-emerald-950/90 px-4 py-3 text-sm text-emerald-100 shadow-2xl" x-cloak>
            <span x-text="toast"></span>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400"><?php echo e(__('Prady Dashboard')); ?></p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white"><?php echo e(__('Financial Operations Command Center')); ?></h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500"><?php echo e(__('Subscriptions, invoicing, collections, documents, and automation — real data only.')); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('invoices.create', ['type' => 'invoice'])); ?>" class="rounded-xl bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white shadow"><?php echo e(__('Create Invoice')); ?></a>
                <a href="<?php echo e(route('invoices.create', ['type' => 'proforma'])); ?>" class="rounded-xl border border-teal-600 px-3 py-2.5 text-sm font-semibold text-teal-700 dark:text-teal-300"><?php echo e(__('Create Proforma')); ?></a>
                <a href="<?php echo e(route('invoices.create', ['type' => 'quotation'])); ?>" class="rounded-xl border border-violet-600 px-3 py-2.5 text-sm font-semibold text-violet-700 dark:text-violet-300"><?php echo e(__('Create Quotation')); ?></a>
                <a href="<?php echo e(route('invoices.create', ['type' => 'receipt'])); ?>" class="rounded-xl border border-emerald-600 px-3 py-2.5 text-sm font-semibold text-emerald-700 dark:text-emerald-300"><?php echo e(__('Create Receipt')); ?></a>
                <form method="POST" action="<?php echo e(route('invoices.generate')); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg"><?php echo e(__('Run billing cycle')); ?></button>
                </form>
                <a href="<?php echo e(route('invoices.index', ['tab' => 'collections'])); ?>" class="rounded-xl border border-amber-600 px-4 py-2.5 text-sm font-semibold text-amber-800 dark:text-amber-200"><?php echo e(__('Collections')); ?></a>
            </div>
        </div>

        <?php echo $__env->make('admin.invoices.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($tab === 'overview'): ?>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6">
                <?php $__currentLoopData = [
                    [__('Total invoiced'), $kpis['totalInvoiced'], 'indigo'],
                    [__('Paid invoices'), $kpis['paid'], 'emerald'],
                    [__('Outstanding'), $kpis['outstanding'], 'amber'],
                    [__('Overdue'), $kpis['overdue'], 'rose'],
                    [__('Month revenue'), $kpis['monthRevenue'], 'violet'],
                    [__('Failed collections'), $kpis['failedCollections'], 'sky'],
                    [__('MRR'), $kpis['mrr'], 'indigo'],
                    [__('ARR'), $kpis['arr'], 'violet'],
                    [__('Collection efficiency'), $kpis['collectionRate'].'%', 'emerald'],
                    [__('Revenue forecast'), $kpis['revenueForecast'], 'amber'],
                    [__('Grace exposure'), $kpis['graceExposure'], 'amber'],
                    [__('Suspension risk'), $kpis['suspensionRisk'].' '.__('tenants'), 'rose'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$title, $value, $tone]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e($title); ?></p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900 dark:text-white"><?php echo e($value); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="grid gap-5 lg:grid-cols-12">
                <div class="lg:col-span-8 space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-sm font-semibold"><?php echo e(__('Invoice trends')); ?></h3>
                            <div class="mt-3 flex h-32 items-end gap-1">
                                <?php $__currentLoopData = $invoiceTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(6, (int) round(($point['issued'] / $trendMax) * 100)); ?>
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t bg-indigo-500" style="height:<?php echo e($h); ?>px"></div>
                                        <span class="text-[9px] text-slate-500"><?php echo e($point['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-sm font-semibold"><?php echo e(__('Revenue collected')); ?></h3>
                            <div class="mt-3 flex h-32 items-end gap-1">
                                <?php $__currentLoopData = $revenueSeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(6, (int) round(($point['value'] / $revenueMax) * 100)); ?>
                                    <div class="flex flex-1 flex-col items-center gap-1">
                                        <div class="w-full rounded-t bg-violet-500" style="height:<?php echo e($h); ?>px"></div>
                                        <span class="text-[9px] text-slate-500"><?php echo e($point['label']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <?php echo $__env->make('admin.invoices.partials.register-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="lg:col-span-4 space-y-4">
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold"><?php echo e(__('Top debtors')); ?></h3>
                        <ul class="mt-2 space-y-2 text-sm">
                            <?php $__empty_1 = true; $__currentLoopData = $topDebtors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li class="flex justify-between"><span><?php echo e($row['tenant']); ?></span><span class="font-mono text-rose-600"><?php echo e($row['balance']); ?></span></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-slate-500"><?php echo e(__('No outstanding balances.')); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold"><?php echo e(__('Upcoming renewals')); ?></h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            <?php $__empty_1 = true; $__currentLoopData = $upcomingRenewals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li><span class="font-medium"><?php echo e($row['tenant']); ?></span> · <?php echo e($row['renewal_date']); ?> · <?php echo e($row['monthly_fee']); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-slate-500"><?php echo e(__('No renewals in the next 30 days.')); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold"><?php echo e(__('Failed deliveries')); ?></h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            <?php $__empty_1 = true; $__currentLoopData = $failedDeliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li><a href="<?php echo e(route('invoices.show', $inv)); ?>" class="text-indigo-600 hover:underline"><?php echo e($inv->invoice_number); ?></a> — <?php echo e($inv->tenant?->company_name); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-slate-500"><?php echo e(__('All deliveries OK.')); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-semibold"><?php echo e(__('Expiring subscriptions')); ?></h3>
                        <ul class="mt-2 space-y-2 text-xs">
                            <?php $__empty_1 = true; $__currentLoopData = $expiringSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li><?php echo e($sub->tenant?->company_name); ?> · <?php echo e($sub->project?->name); ?> · <?php echo e($sub->renewal_date?->format('M j') ?? $sub->license_status); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-slate-500"><?php echo e(__('No expiring subscriptions.')); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php elseif($tab === 'recurring'): ?>
            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="prady-table">
                    <thead><tr>
                        <th><?php echo e(__('Schedule')); ?></th><th><?php echo e(__('Tenant')); ?></th><th><?php echo e(__('Amount')); ?></th>
                        <th><?php echo e(__('Frequency')); ?></th><th><?php echo e(__('Next run')); ?></th><th><?php echo e(__('Enabled')); ?></th>
                    </tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-semibold"><?php echo e($schedule->name); ?></td>
                                <td><?php echo e($schedule->tenant?->company_name); ?></td>
                                <td class="font-mono text-xs"><?php echo e(\App\Models\TenantInvoice::formatMoney($schedule->totalWithTax())); ?></td>
                                <td><?php echo e($schedule->frequencyLabel()); ?></td>
                                <td><?php echo e($schedule->next_run_at?->format('M j, H:i') ?? '—'); ?></td>
                                <td>
                                    <form method="POST" action="<?php echo e(route('invoices.schedules.toggle', $schedule)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="text-xs font-semibold <?php echo e($schedule->enabled ? 'text-emerald-600' : 'text-slate-400'); ?>"><?php echo e($schedule->enabled ? __('On') : __('Off')); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="py-8 text-center text-slate-500"><?php echo e(__('No recurring schedules.')); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($tab === 'collections'): ?>
            <?php echo $__env->make('admin.invoices.partials.collections-dashboard', ['collections' => $collections], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($tab === 'templates'): ?>
            <div class="grid gap-4 md:grid-cols-2">
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-2xl border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <p class="text-xs uppercase text-slate-500"><?php echo e($template->type); ?> · <?php echo e($template->style); ?></p>
                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600"><?php echo e($template->paper_size); ?> · <?php echo e($template->orientation); ?></span>
                            <?php if($template->is_default): ?>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800"><?php echo e(__('Default')); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo e(route('invoices.templates.sample-preview', $template)); ?>" target="_blank" class="mb-2 inline-block text-xs font-semibold text-indigo-600 hover:underline"><?php echo e(__('Template preview')); ?> →</a>
                        <form method="POST" action="<?php echo e(route('invoices.templates.update', $template)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <input name="name" value="<?php echo e($template->name); ?>" class="mt-1 w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-500"><?php echo e(__('Paper')); ?></label>
                                <select name="paper_size" class="mt-0.5 w-full rounded text-xs dark:bg-slate-950">
                                    <?php $__currentLoopData = ['A4' => 'A4', 'A5' => 'A5']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val); ?>" <?php if(strtoupper($template->paper_size) === $val): echo 'selected'; endif; ?>><?php echo e($lab); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-500"><?php echo e(__('Orientation')); ?></label>
                                <select name="orientation" class="mt-0.5 w-full rounded text-xs dark:bg-slate-950">
                                    <option value="portrait" <?php if($template->orientation === 'portrait'): echo 'selected'; endif; ?>><?php echo e(__('Portrait')); ?></option>
                                    <option value="landscape" <?php if($template->orientation === 'landscape'): echo 'selected'; endif; ?>><?php echo e(__('Landscape')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <input name="primary_color" value="<?php echo e($template->brandingValue('primary_color', '#4f46e5')); ?>" placeholder="<?php echo e(__('Primary')); ?>" class="rounded text-xs">
                            <input name="accent_color" value="<?php echo e($template->brandingValue('accent_color', '#f59e0b')); ?>" placeholder="<?php echo e(__('Accent')); ?>" class="rounded text-xs">
                        </div>
                        <textarea name="footer_text" rows="2" class="mt-2 w-full rounded text-xs" placeholder="<?php echo e(__('Footer')); ?>"><?php echo e($template->brandingValue('footer_text')); ?></textarea>
                        <label class="mt-2 flex items-center gap-2 text-xs"><input type="checkbox" name="show_qr" value="1" <?php if($template->brandingValue('show_qr')): echo 'checked'; endif; ?>> <?php echo e(__('QR code')); ?></label>
                        <label class="mt-2 flex items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                            <input type="checkbox" name="is_default" value="1" <?php if($template->is_default): echo 'checked'; endif; ?>> <?php echo e(__('Default for this document type')); ?>

                        </label>
                        <button type="submit" class="mt-3 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Save template')); ?></button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php elseif($tab === 'automation'): ?>
            <form method="POST" action="<?php echo e(route('invoices.automation.update')); ?>" class="max-w-xl rounded-2xl border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <h3 class="text-sm font-semibold"><?php echo e(__('Billing automation rules')); ?></h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <?php $__currentLoopData = [
                        'reminder_after_days' => __('Reminder after (days)'),
                        'penalty_after_days' => __('Penalty after (days)'),
                        'suspension_after_days' => __('Suspension after (days)'),
                        'grace_period_days' => __('Grace period (days)'),
                        'penalty_percent' => __('Penalty %'),
                        'vat_percent' => __('VAT %'),
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <label class="text-xs text-slate-500"><?php echo e($label); ?></label>
                            <input type="number" step="0.01" name="<?php echo e($field); ?>" value="<?php echo e($automationRules->$field); ?>" class="mt-1 w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    <?php $__currentLoopData = ['recurring_enabled','auto_send_invoices','auto_send_receipts','auto_generate_pdf']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2"><input type="checkbox" name="<?php echo e($flag); ?>" value="1" <?php if($automationRules->$flag): echo 'checked'; endif; ?>> <?php echo e(str_replace('_',' ',ucfirst($flag))); ?></label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button type="submit" class="mt-4 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white"><?php echo e(__('Save rules')); ?></button>
            </form>
        <?php elseif($tab === 'payments'): ?>
            <?php echo $__env->make('admin.invoices.partials.payments-inbox', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($tab === 'activity'): ?>
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
        <?php else: ?>
            <?php echo $__env->make('admin.invoices.partials.register-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/admin/invoices/index.blade.php ENDPATH**/ ?>