<?php
    $currency = $billingKpi['currency'] ?? 'KES';
?>

<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('MRR (subscriptions)')); ?></p>
        <p class="mt-2 text-xl font-semibold tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format($billingKpi['mrr'] ?? 0, 2)); ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Outstanding')); ?></p>
        <p class="mt-2 text-xl font-semibold tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format($billingKpi['outstanding'] ?? 0, 2)); ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Next renewal')); ?></p>
        <p class="mt-2 text-lg font-semibold"><?php echo e(optional($billingKpi['next_renewal'] ?? null)->toFormattedDateString() ?? '—'); ?></p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Next invoice due')); ?></p>
        <p class="mt-2 text-lg font-semibold"><?php echo e(optional($billingKpi['next_due'] ?? null)->toFormattedDateString() ?? '—'); ?></p>
    </div>
</div>

<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 dark:border-emerald-900 dark:bg-emerald-950/20">
    <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100"><?php echo e(__('Record payment')); ?></h3>
    <p class="mt-1 text-xs text-slate-600"><?php echo e(__('Record against a specific invoice from the invoice page, or save an unreconciled payment to the Payment Inbox.')); ?></p>
    <div class="mt-3">
        <?php echo $__env->make('admin.invoices.partials.record-payment-form', [
            'formAction' => route('invoices.payments.record'),
            'defaultTenantId' => $tenant->id,
            'filterTenants' => collect([$tenant]),
            'paymentSources' => \App\Support\Billing\PaymentSource::all(),
            'compact' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <a href="<?php echo e(route('invoices.index', ['tab' => 'payments', 'tenant_id' => $tenant->id])); ?>" class="mt-2 inline-block text-xs font-semibold text-indigo-600"><?php echo e(__('Open Payment Inbox for this tenant →')); ?></a>
</div>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo e(__('Centralized billing from project subscriptions, modules, integrations, and usage.')); ?></p>
    <?php if($billableSubscriptions->isNotEmpty()): ?>
        <form method="post" action="<?php echo e(route('tenants.billing.generate-draft', $tenant)); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <?php echo e(__('Generate draft invoice')); ?>

            </button>
        </form>
    <?php endif; ?>
    <form method="post" action="<?php echo e(route('tenants.billing.generate-statement', $tenant)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200">
            <?php echo e(__('Generate account statement')); ?>

        </button>
    </form>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <form method="post" action="<?php echo e(route('tenants.billing-profile.update', $tenant)); ?>" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(__('Billing profile')); ?></h3>
        </div>
        <div class="space-y-3 p-4 text-sm">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Contact name')); ?></label>
                <input name="billing_contact_name" value="<?php echo e(old('billing_contact_name', $tenant->billing_contact_name)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Billing email')); ?></label>
                <input type="email" name="billing_email" value="<?php echo e(old('billing_email', $tenant->billing_email)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Phone')); ?></label>
                <input name="billing_phone" value="<?php echo e(old('billing_phone', $tenant->billing_phone)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Address')); ?></label>
                <textarea name="billing_address" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"><?php echo e(old('billing_address', $tenant->billing_address)); ?></textarea>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('Tax PIN')); ?></label>
                    <input name="billing_tax_pin" value="<?php echo e(old('billing_tax_pin', $tenant->billing_tax_pin)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('Preferred currency')); ?></label>
                    <input name="billing_preferred_currency" maxlength="3" value="<?php echo e(old('billing_preferred_currency', $tenant->billing_preferred_currency)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Payment terms')); ?></label>
                <input name="billing_payment_terms" value="<?php echo e(old('billing_payment_terms', $tenant->billing_payment_terms)); ?>" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="billing_tax_exempt" value="1" <?php if(old('billing_tax_exempt', $tenant->billing_tax_exempt)): echo 'checked'; endif; ?> class="rounded border-gray-300" />
                <span><?php echo e(__('Tax exempt')); ?></span>
            </label>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('Notes')); ?></label>
                <textarea name="billing_notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"><?php echo e(old('billing_notes', $tenant->billing_notes)); ?></textarea>
            </div>
            <button type="submit" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:text-indigo-300"><?php echo e(__('Save profile')); ?></button>
        </div>
    </form>

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(__('Active subscriptions')); ?></h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <?php $__empty_1 = true; $__currentLoopData = $billableSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="px-4 py-3 text-sm">
                        <span class="font-medium"><?php echo e($sub->project?->name); ?></span>
                        <span class="text-gray-500"> — <?php echo e($sub->package_name); ?></span>
                        <p class="mt-1 text-xs text-gray-500">
                            <?php echo e(strtoupper($sub->currency ?? $currency)); ?> <?php echo e(number_format((float) ($sub->monthly_fee ?? 0), 2)); ?>/<?php echo e($sub->billing_cycle); ?>

                            <?php if((float) ($sub->setup_fee ?? 0) > 0): ?>
                                · <?php echo e(__('Setup')); ?> <?php echo e(number_format((float) $sub->setup_fee, 2)); ?>

                                <?php if($draftGenerator->setupFeeAlreadyInvoiced($tenant, $sub)): ?>
                                    <span class="text-emerald-600">(<?php echo e(__('invoiced')); ?>)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="px-4 py-6 text-sm text-gray-500"><?php echo e(__('No billable subscriptions. Suspended or disabled products are excluded.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(__('Billable modules')); ?></h3>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                <?php $hasModules = false; ?>
                <?php $__currentLoopData = $billableSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $sub->moduleSubscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($mod->enabled && $mod->subscribed): ?>
                            <?php
                                $hasModules = true;
                                $price = $mod->monthly_price_override ?? $mod->projectModule?->monthly_price ?? 0;
                            ?>
                            <?php if((float) $price > 0): ?>
                                <li class="px-4 py-2 text-sm"><?php echo e($sub->project?->name); ?> — <?php echo e($mod->projectModule?->name); ?>: <?php echo e(number_format((float) $price, 2)); ?></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if (! ($hasModules)): ?>
                    <li class="px-4 py-6 text-sm text-gray-500"><?php echo e(__('No subscribed modules with pricing.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php if(! empty($tenantCollections)): ?>
    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <h3 class="text-sm font-semibold text-rose-900 dark:text-rose-100"><?php echo e(__('Collections overview')); ?></h3>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Unpaid invoices')); ?></dt><dd class="font-semibold"><?php echo e($tenantCollections['unpaid_invoices']->count()); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Overdue')); ?></dt><dd class="font-semibold text-rose-700"><?php echo e($tenantCollections['overdue_invoices']->count()); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Open promises')); ?></dt><dd class="font-semibold"><?php echo e($tenantCollections['promises']->count()); ?></dd></div>
                <div><dt class="text-xs text-slate-500"><?php echo e(__('Next follow-up')); ?></dt><dd class="font-semibold"><?php echo e(optional($tenantCollections['next_follow_up'])->format('M j, Y') ?? '—'); ?></dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold"><?php echo e(__('Open collection notes')); ?></h3>
            <ul class="mt-2 max-h-40 space-y-2 overflow-y-auto text-xs">
                <?php $__empty_1 = true; $__currentLoopData = $tenantCollections['open_collection_notes'] ?? $tenantCollections['collection_notes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li>
                        <a href="<?php echo e(route('invoices.show', $note->invoice)); ?>" class="font-semibold text-indigo-600"><?php echo e($note->invoice?->invoice_number); ?></a>
                        <span class="text-slate-500 capitalize"> · <?php echo e(str_replace('_', ' ', $note->outcome ?? $note->note_type)); ?></span>
                        <p><?php echo e(Str::limit($note->displayText(), 70)); ?></p>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-slate-500"><?php echo e(__('No collection notes.')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php if($tenantCollections['unpaid_invoices']->isNotEmpty()): ?>
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-gray-900">
            <h3 class="text-sm font-semibold"><?php echo e(__('Unpaid invoices')); ?></h3>
            <ul class="mt-2 space-y-1 text-sm">
                <?php $__currentLoopData = $tenantCollections['unpaid_invoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between gap-2">
                        <a href="<?php echo e(route('invoices.show', $inv)); ?>#collections" class="font-mono text-indigo-600"><?php echo e($inv->invoice_number); ?></a>
                        <span class="font-mono text-xs"><?php echo e($inv->formattedBalance()); ?> · <?php echo e($inv->due_date?->format('M j') ?? '—'); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if($tenantCollections['overdue_invoices']->isNotEmpty()): ?>
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/30 p-4 dark:border-amber-900 dark:bg-amber-950/20">
            <h3 class="text-sm font-semibold"><?php echo e(__('Overdue invoices')); ?></h3>
            <ul class="mt-2 space-y-1 text-sm">
                <?php $__currentLoopData = $tenantCollections['overdue_invoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between">
                        <a href="<?php echo e(route('invoices.show', $inv)); ?>" class="font-mono text-indigo-600"><?php echo e($inv->invoice_number); ?></a>
                        <span class="font-mono"><?php echo e($inv->formattedBalance()); ?> · <?php echo e($inv->due_date?->format('M j')); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if($tenantCollections['promises']->isNotEmpty()): ?>
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 dark:border-emerald-900">
            <h3 class="text-sm font-semibold"><?php echo e(__('Promise to pay')); ?></h3>
            <ul class="mt-2 space-y-1 text-sm">
                <?php $__currentLoopData = $tenantCollections['promises']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('invoices.show', $note->invoice)); ?>" class="text-indigo-600"><?php echo e($note->invoice?->invoice_number); ?></a>
                        — <?php echo e($note->promise_to_pay_date?->format('M j, Y')); ?>

                        <?php if($note->promised_amount): ?>
                            (<?php echo e(\App\Models\TenantInvoice::formatMoney((float) $note->promised_amount, $note->invoice?->currency)); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e(__('Invoices')); ?></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2"><?php echo e(__('Invoice')); ?></th>
                    <th class="px-4 py-2"><?php echo e(__('Issue')); ?></th>
                    <th class="px-4 py-2"><?php echo e(__('Due')); ?></th>
                    <th class="px-4 py-2 text-right"><?php echo e(__('Total')); ?></th>
                    <th class="px-4 py-2 text-right"><?php echo e(__('Balance')); ?></th>
                    <th class="px-4 py-2"><?php echo e(__('Status')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <?php $__empty_1 = true; $__currentLoopData = $tenant->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-4 py-2">
                            <a href="<?php echo e(route('invoices.show', $inv)); ?>" class="font-mono text-xs text-indigo-600 hover:underline"><?php echo e($inv->invoice_number); ?></a>
                        </td>
                        <td class="px-4 py-2"><?php echo e(optional($inv->issue_date ?? $inv->issued_at)->toFormattedDateString() ?? '—'); ?></td>
                        <td class="px-4 py-2"><?php echo e(optional($inv->due_date)->toFormattedDateString() ?? '—'); ?></td>
                        <td class="px-4 py-2 text-right tabular-nums"><?php echo e($inv->currency ?? $currency); ?> <?php echo e(number_format($inv->invoiceTotal(), 2)); ?></td>
                        <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format($inv->balanceDue(), 2)); ?></td>
                        <td class="px-4 py-2 capitalize"><?php echo e($inv->statusLabel()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500"><?php echo e(__('No invoices yet. Generate a draft when billable items exist.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/tenants/partials/ops/billing.blade.php ENDPATH**/ ?>