<?php
    $kpis = $paymentKpis ?? [];
    $inboxMeta = $paymentInboxMeta ?? [];
?>

<div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-amber-600"><?php echo e(__('Unreconciled')); ?></p>
        <p class="mt-1 text-lg font-semibold tabular-nums"><?php echo e($kpis['unreconciled_count'] ?? 0); ?></p>
        <p class="text-xs text-slate-500"><?php echo e(\App\Models\TenantInvoice::formatMoney($kpis['unreconciled_amount'] ?? 0)); ?></p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-emerald-600"><?php echo e(__('Matched today')); ?></p>
        <p class="mt-1 text-lg font-semibold"><?php echo e($kpis['matched_today'] ?? 0); ?></p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-rose-600"><?php echo e(__('Duplicates')); ?></p>
        <p class="mt-1 text-lg font-semibold"><?php echo e($kpis['duplicates'] ?? 0); ?></p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Ignored')); ?></p>
        <p class="mt-1 text-lg font-semibold"><?php echo e($kpis['ignored'] ?? 0); ?></p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-indigo-600"><?php echo e(__('This month')); ?></p>
        <p class="mt-1 text-lg font-semibold"><?php echo e($kpis['payments_this_month'] ?? 0); ?></p>
        <p class="text-xs text-slate-500"><?php echo e(\App\Models\TenantInvoice::formatMoney($kpis['month_collected'] ?? 0)); ?></p>
    </div>
    <div class="rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Avg reconcile')); ?></p>
        <p class="mt-1 text-lg font-semibold"><?php echo e($kpis['avg_reconciliation_hours'] ?? 0); ?>h</p>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-12">
    <div class="lg:col-span-4">
        <?php echo $__env->make('admin.invoices.partials.record-payment-form', [
            'filterTenants' => $filterTenants,
            'paymentSources' => $paymentSources,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <div class="lg:col-span-8">
        <form method="get" action="<?php echo e(route('invoices.index')); ?>" class="mb-4 space-y-3 rounded-xl border bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
            <input type="hidden" name="tab" value="payments">
            <div class="flex flex-wrap gap-1">
                <?php
                    $statusChips = [
                        '' => __('All'),
                        'unreconciled' => __('Unreconciled'),
                        'matched' => __('Matched'),
                        'partially_matched' => __('Partial'),
                        'duplicate' => __('Duplicate'),
                        'ignored' => __('Ignored'),
                    ];
                ?>
                <?php $__currentLoopData = $statusChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $chipQuery = array_merge(request()->except('page', 'reconciliation_status'), ['tab' => 'payments']);
                        if ($value !== '') {
                            $chipQuery['reconciliation_status'] = $value;
                        }
                    ?>
                    <a href="<?php echo e(route('invoices.index', $chipQuery)); ?>"
                       class="rounded-full px-2.5 py-1 text-[11px] font-semibold <?php echo e(request('reconciliation_status', '') === $value ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'); ?>">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Source')); ?></label>
                    <select name="source" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                        <option value=""><?php echo e(__('All sources')); ?></option>
                        <?php $__currentLoopData = $paymentSources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($src); ?>" <?php if(request('source') === $src): echo 'selected'; endif; ?>><?php echo e(\App\Support\Billing\PaymentSource::label($src)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Tenant')); ?></label>
                    <select name="tenant_id" class="mt-0.5 block min-w-[140px] rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                        <option value=""><?php echo e(__('All tenants')); ?></option>
                        <?php $__currentLoopData = $filterTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php if((string) request('tenant_id') === (string) $t->id): echo 'selected'; endif; ?>><?php echo e($t->company_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('From')); ?></label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('To')); ?></label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="mt-0.5 block rounded-lg border-slate-300 text-xs dark:bg-slate-950">
                </div>
                <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Apply filters')); ?></button>
                <a href="<?php echo e(route('invoices.index', ['tab' => 'payments'])); ?>" class="rounded-lg border px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-700"><?php echo e(__('Reset')); ?></a>
            </div>
        </form>

        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $paymentInbox; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $meta = $inboxMeta[$pay->id] ?? ['suggestions' => [], 'duplicate' => null];
                    $duplicateOf = $meta['duplicate'] ?? null;
                    $suggestions = $meta['suggestions'] ?? [];
                    $canMatch = $pay->reconciliation_status === 'unreconciled';
                    $canSplit = $canMatch || ($pay->reconciliation_status === 'partially_matched' && $pay->remainingToAllocate() > 0.009);
                ?>
                <article id="payment-<?php echo e($pay->id); ?>" class="rounded-2xl border bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b px-4 py-3 dark:border-slate-800">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-semibold"><?php echo e($pay->formattedAmount()); ?></span>
                                <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $pay->reconciliationVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pay->reconciliationVariant())]); ?><?php echo e($pay->reconciliationLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?php echo e($pay->sourceLabel()); ?></span>
                                <?php if($duplicateOf && $pay->reconciliation_status !== 'duplicate'): ?>
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-200" title="<?php echo e(__('Matches payment :ref', ['ref' => $duplicateOf->displayId()])); ?>">
                                        <?php echo e(__('Possible duplicate')); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                <?php echo e(optional($pay->paid_at)->format('M j, Y g:i A') ?? '—'); ?>

                                · <?php echo e(__('Ref')); ?>: <span class="font-mono"><?php echo e($pay->reference ?? '—'); ?></span>
                                <?php if($pay->bank_source): ?>
                                    · <?php echo e($pay->bank_source); ?>

                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <?php if($pay->isReconciled()): ?>
                                <form method="post" action="<?php echo e(route('invoices.payments.reverse', $pay)); ?>"><?php echo csrf_field(); ?>
                                    <button type="submit" class="font-semibold text-rose-600 hover:underline"><?php echo e(__('Reverse')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($duplicateOf && $pay->reconciliation_status !== 'duplicate'): ?>
                                <form method="post" action="<?php echo e(route('invoices.payments.duplicate', $pay)); ?>"><?php echo csrf_field(); ?>
                                    <button type="submit" class="font-semibold text-amber-700 hover:underline"><?php echo e(__('Mark duplicate')); ?></button>
                                </form>
                            <?php elseif($pay->reconciliation_status !== 'duplicate'): ?>
                                <form method="post" action="<?php echo e(route('invoices.payments.duplicate', $pay)); ?>"><?php echo csrf_field(); ?>
                                    <button type="submit" class="font-semibold text-amber-600 hover:underline"><?php echo e(__('Flag duplicate')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($pay->reconciliation_status !== 'ignored'): ?>
                                <form method="post" action="<?php echo e(route('invoices.payments.ignore', $pay)); ?>"><?php echo csrf_field(); ?>
                                    <button type="submit" class="font-semibold text-slate-500 hover:underline"><?php echo e(__('Ignore')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($canSplit): ?>
                                <button type="button" class="font-semibold text-indigo-600 hover:underline" onclick="document.getElementById('split-dialog-<?php echo e($pay->id); ?>')?.showModal()">
                                    <?php echo e(__('Split')); ?>

                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid gap-4 px-4 py-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Payer')); ?></p>
                            <p class="mt-0.5 text-sm font-medium"><?php echo e($pay->payer_name ?? $pay->tenant?->company_name ?? '—'); ?></p>
                            <?php if($pay->payer_phone): ?>
                                <p class="text-xs text-slate-500"><?php echo e($pay->payer_phone); ?></p>
                            <?php endif; ?>
                            <?php if($pay->payer_email): ?>
                                <p class="text-xs text-slate-500"><?php echo e($pay->payer_email); ?></p>
                            <?php endif; ?>
                            <?php if($pay->tenant && $pay->payer_name): ?>
                                <p class="text-xs text-indigo-600"><?php echo e($pay->tenant->company_name); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Details')); ?></p>
                            <?php if($pay->narration): ?>
                                <p class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"><?php echo e($pay->narration); ?></p>
                            <?php endif; ?>
                            <?php if($pay->notes): ?>
                                <p class="mt-1 text-xs italic text-slate-500"><?php echo e(Str::limit($pay->notes, 120)); ?></p>
                            <?php endif; ?>
                            <?php if((float) $pay->unapplied_amount > 0): ?>
                                <p class="mt-1 text-xs font-semibold text-amber-600"><?php echo e(__('Unapplied credit')); ?>: <?php echo e(\App\Models\TenantInvoice::formatMoney((float) $pay->unapplied_amount, $pay->currency)); ?></p>
                            <?php endif; ?>
                            <?php if($pay->allocations->isNotEmpty()): ?>
                                <ul class="mt-1 space-y-0.5 text-xs text-slate-500">
                                    <?php $__currentLoopData = $pay->allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($alloc->invoice?->invoice_number ?? '#'); ?> — <?php echo e(\App\Models\TenantInvoice::formatMoney((float) $alloc->amount, $pay->currency)); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Method / gateway')); ?></p>
                            <p class="mt-0.5 text-sm"><?php echo e($pay->method ?? $pay->gatewayLabel()); ?></p>
                            <?php if($pay->matched_at): ?>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Matched')); ?> <?php echo e($pay->matched_at->diffForHumans()); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($canMatch && count($suggestions) > 0): ?>
                        <div class="border-t bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/50">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600"><?php echo e(__('Suggested matches')); ?></p>
                            <div class="mt-2 space-y-2">
                                <?php $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sug): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-indigo-100 bg-white px-3 py-2 dark:border-indigo-900/40 dark:bg-slate-900">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="<?php echo e(route('invoices.show', $sug['invoice_public_id'])); ?>" class="font-mono text-xs font-semibold text-indigo-600 hover:underline"><?php echo e($sug['invoice_number']); ?></a>
                                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-bold text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200"><?php echo e($sug['score']); ?> <?php echo e(__('pts')); ?></span>
                                            </div>
                                            <p class="text-xs text-slate-600"><?php echo e($sug['tenant']); ?> · <?php echo e(__('Balance')); ?> <?php echo e($sug['balance']); ?> · <?php echo e(__('Due')); ?> <?php echo e($sug['due_date']); ?></p>
                                            <?php if(! empty($sug['reasons'])): ?>
                                                <p class="mt-0.5 text-[10px] text-slate-500"><?php echo e(implode(' · ', $sug['reasons'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap items-center gap-1">
                                            <?php
                                                $fullPayAmount = min((float) $pay->remainingToAllocate(), (float) $sug['balance_raw']);
                                            ?>
                                            <form method="post" action="<?php echo e(route('invoices.payments.match', $pay)); ?>" class="inline"><?php echo csrf_field(); ?>
                                                <input type="hidden" name="invoice_id" value="<?php echo e($sug['invoice_id']); ?>">
                                                <?php if($sug['is_partial']): ?>
                                                    <input type="hidden" name="amount" value="<?php echo e($fullPayAmount); ?>">
                                                <?php endif; ?>
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-semibold text-white">
                                                    <?php echo e($sug['is_partial'] ? __('Match (pay balance)') : __('Match')); ?>

                                                </button>
                                            </form>
                                            <?php if($sug['is_partial']): ?>
                                                <form method="post" action="<?php echo e(route('invoices.payments.match', $pay)); ?>" class="inline flex items-center gap-1"><?php echo csrf_field(); ?>
                                                    <input type="hidden" name="invoice_id" value="<?php echo e($sug['invoice_id']); ?>">
                                                    <input type="number" step="0.01" name="amount" value="<?php echo e($sug['suggested_amount']); ?>" class="w-20 rounded border-slate-300 text-[11px] dark:bg-slate-950" title="<?php echo e(__('Amount to apply')); ?>">
                                                    <button type="submit" class="rounded-lg border border-amber-500 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:text-amber-300">
                                                        <?php echo e(__('Partial match')); ?>

                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php elseif($canMatch): ?>
                        <div class="border-t px-4 py-2 text-xs text-slate-500 dark:border-slate-800">
                            <?php echo e(__('No automatic suggestions — use manual match below or split.')); ?>

                            <form method="post" action="<?php echo e(route('invoices.payments.match', $pay)); ?>" class="mt-2 flex flex-wrap gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="number" name="invoice_id" placeholder="<?php echo e(__('Invoice ID')); ?>" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950" required>
                                <input type="number" step="0.01" name="amount" placeholder="<?php echo e(__('Amount (optional)')); ?>" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950">
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white"><?php echo e(__('Manual match')); ?></button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if($canSplit): ?>
                        <dialog id="split-dialog-<?php echo e($pay->id); ?>" class="w-full max-w-lg rounded-2xl border bg-white p-5 shadow-xl backdrop:bg-black/50 dark:border-slate-700 dark:bg-slate-900">
                            <form method="post" action="<?php echo e(route('invoices.payments.split', $pay)); ?>" data-split-form data-split-max="<?php echo e((float) $pay->remainingToAllocate()); ?>" onsubmit="return validateSplitForm(this)">
                                <?php echo csrf_field(); ?>
                                <h3 class="text-sm font-semibold"><?php echo e(__('Split payment')); ?></h3>
                                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Payment')); ?> <?php echo e($pay->displayId()); ?> · <?php echo e(__('Max to allocate')); ?> <span class="font-mono font-semibold"><?php echo e(number_format((float) $pay->remainingToAllocate(), 2)); ?></span></p>
                                <div class="mt-4 space-y-2">
                                    <?php for($i = 0; $i < 4; $i++): ?>
                                        <div class="flex gap-2">
                                            <input type="number" name="allocations[<?php echo e($i); ?>][invoice_id]" placeholder="<?php echo e(__('Invoice ID')); ?>" class="w-28 rounded border-slate-300 text-xs dark:bg-slate-950">
                                            <input type="number" step="0.01" name="allocations[<?php echo e($i); ?>][amount]" placeholder="<?php echo e(__('Amount')); ?>" class="flex-1 rounded border-slate-300 text-xs dark:bg-slate-950" data-split-amount-input oninput="updateSplitRemaining(this.closest('[data-split-form]'))">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Remaining unallocated')); ?>: <span class="font-mono font-semibold text-emerald-600" data-split-remaining><?php echo e(number_format((float) $pay->remainingToAllocate(), 2)); ?></span></p>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" class="rounded-lg border px-3 py-1.5 text-xs" onclick="this.closest('dialog')?.close()"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Apply split')); ?></button>
                                </div>
                            </form>
                        </dialog>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="rounded-2xl border border-dashed px-6 py-12 text-center text-sm text-slate-500 dark:border-slate-700"><?php echo e(__('No payments in inbox.')); ?></p>
            <?php endif; ?>
        </div>

        <div class="mt-4"><?php echo e($paymentInbox->links()); ?></div>
    </div>
</div>

<script>
    function updateSplitRemaining(formEl) {
        if (!formEl) return;
        const maxAmount = parseFloat(formEl.dataset.splitMax) || 0;
        let sum = 0;
        formEl.querySelectorAll('[data-split-amount-input]').forEach((input) => {
            const row = input.closest('.flex');
            const invId = row?.querySelector('[name*="invoice_id"]')?.value;
            const v = parseFloat(input.value);
            if (invId && !isNaN(v) && v > 0) sum += v;
        });
        const remaining = Math.max(0, maxAmount - sum);
        const el = formEl.querySelector('[data-split-remaining]');
        if (el) {
            el.textContent = remaining.toFixed(2);
            el.classList.toggle('text-rose-600', sum > maxAmount + 0.009);
            el.classList.toggle('text-emerald-600', sum <= maxAmount + 0.009);
        }
    }
    function validateSplitForm(formEl) {
        const maxAmount = parseFloat(formEl.dataset.splitMax) || 0;
        let sum = 0;
        let hasLine = false;
        formEl.querySelectorAll('[data-split-amount-input]').forEach((input) => {
            const row = input.closest('.flex');
            const invId = row?.querySelector('[name*="invoice_id"]')?.value;
            const amt = parseFloat(input.value);
            if (invId && !isNaN(amt) && amt > 0) {
                hasLine = true;
                sum += amt;
            }
        });
        if (!hasLine) {
            alert(<?php echo json_encode(__('Add at least one invoice allocation.'), 15, 512) ?>);
            return false;
        }
        if (sum > maxAmount + 0.009) {
            alert(<?php echo json_encode(__('Split total exceeds available payment amount.'), 15, 512) ?>);
            return false;
        }
        return true;
    }
</script>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/partials/payments-inbox.blade.php ENDPATH**/ ?>