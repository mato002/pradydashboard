<?php
    $typeLabels = [
        'invoice' => __('Invoice'),
        'proforma' => __('Proforma'),
        'quotation' => __('Quotation'),
        'receipt' => __('Receipt'),
    ];
    $typeBadgeVariant = match ($documentType) {
        'quotation' => 'purple',
        'proforma' => 'warning',
        'receipt' => 'success',
        default => 'info',
    };
    $heading = __('Create :type', ['type' => $typeLabels[$documentType] ?? __('Document')]);
    $defaultLineItem = [
        'description' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'discount' => 0,
        'tax_rate' => 0,
        'item_type' => 'custom',
    ];
    $initialLineItems = old('line_items');
    if (! is_array($initialLineItems) || $initialLineItems === []) {
        $initialLineItems = [$defaultLineItem];
    }
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $heading,'subheading' => __('Manual financial document')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heading),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manual financial document'))]); ?>
    <div
        x-data="manualDocumentForm(<?php echo \Illuminate\Support\Js::from([
            'documentType' => $documentType,
            'currency' => old('currency', $defaultCurrency),
            'lineItems' => $initialLineItems,
            'tenantProfileBase' => url('/invoices/tenants'),
            'oldTenantId' => old('tenant_id', ''),
            'oldSubscriptionId' => old('tenant_project_subscription_id', ''),
            'clientName' => old('manual_client_name', ''),
            'clientEmail' => old('manual_client_email', ''),
            'clientPhone' => old('manual_client_phone', ''),
            'clientAddress' => old('manual_client_address', ''),
            'issueDate' => old('issue_date', now()->toDateString()),
            'dueDate' => old('due_date', ''),
            'paymentDate' => old('payment_date', now()->toDateString()),
            'notes' => old('notes', ''),
            'linkedInvoiceId' => old('linked_invoice_id', ''),
            'receiptAmount' => old('amount_received', 0),
            'receiptLineDesc' => old('line_description', __('Payment received')),
            'amountPaid' => old('amount_paid', 0),
            'previewCompany' => $previewCompany,
            'paymentOptions' => $paymentOptions,
            'numberPrefix' => \App\Support\Billing\BillingDocumentType::numberPrefix($documentType),
            'previewUrl' => route('invoices.manual.preview'),
            'initialPreviewHtml' => $initialPreviewHtml ?? '',
            'initialPreviewPaperSize' => $initialPreviewPaperSize ?? 'A5',
            'openInvoices' => $openInvoicesPicker ?? [],
            'typeLabels' => $typeLabels,
            'i18n' => [
                'clientName' => __('Client name'),
                'paymentReceived' => __('Payment received'),
            ],
        ])->toHtml() ?>)"
    >
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="<?php echo e(route('invoices.index')); ?>" class="text-sm text-indigo-600 hover:underline">← <?php echo e(__('Financial operations')); ?></a>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="previewOpen = true"
                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 lg:hidden dark:border-indigo-800 dark:bg-slate-900 dark:text-indigo-300"
            >
                <?php echo e(__('View Preview')); ?>

            </button>
            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $typeBadgeVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($typeBadgeVariant)]); ?><?php echo e($typeLabels[$documentType] ?? $documentType); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
        </div>
    </div>

    <div
        x-show="previewOpen"
        x-cloak
        @click="previewOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
        aria-hidden="true"
    ></div>

    <div
        id="manual-document-create-layout"
        data-testid="manual-document-create-layout"
        class="manual-document-create-layout"
    >
    <div class="manual-document-create-form">
    <form
        method="post"
        action="<?php echo e(route('invoices.manual.store')); ?>"
        class="space-y-6"
    >
        <?php echo csrf_field(); ?>
        <input type="hidden" name="document_type" value="<?php echo e($documentType); ?>">

        
        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Client')); ?></h3>
            <div class="mt-4">
                <label class="text-xs font-medium text-slate-500"><?php echo e(__('Tenant')); ?></label>
                <select
                    name="tenant_id"
                    x-model="tenantId"
                    @change="onTenantChange()"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950"
                >
                    <option value=""><?php echo e(__('— No tenant (manual client) —')); ?></option>
                    <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>" <?php if(old('tenant_id') == $t->id): echo 'selected'; endif; ?>><?php echo e($t->company_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div
                x-show="!tenantId"
                x-cloak
                class="mt-4 flex gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100"
                role="alert"
            >
                <span class="text-lg leading-none" aria-hidden="true">⚠</span>
                <div>
                    <p class="font-semibold"><?php echo e(__('No tenant selected')); ?></p>
                    <p class="mt-0.5 text-xs opacity-90"><?php echo e(__('This document will not appear under a tenant account. Enter manual client details below. Linked receipts cannot attach to walk-in invoices from this form.')); ?></p>
                </div>
            </div>

            <div x-show="tenantId" x-cloak class="mt-4">
                <div x-show="profileLoading" class="text-xs text-slate-500"><?php echo e(__('Loading billing profile…')); ?></div>
                <div
                    x-show="tenantProfile && !profileLoading"
                    class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/30"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-800 dark:text-emerald-300"><?php echo e(__('Billing profile (auto-filled)')); ?></p>
                    <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-slate-500"><?php echo e(__('Company')); ?></dt>
                            <dd class="font-medium text-slate-900 dark:text-white" x-text="tenantProfile?.company_name || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500"><?php echo e(__('Contact')); ?></dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_contact_name || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500"><?php echo e(__('Email')); ?></dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_email || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500"><?php echo e(__('Phone')); ?></dt>
                            <dd class="text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_phone || '—'"></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-500"><?php echo e(__('Address')); ?></dt>
                            <dd class="whitespace-pre-line text-slate-800 dark:text-slate-200" x-text="tenantProfile?.billing_address || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500"><?php echo e(__('Currency')); ?></dt>
                            <dd class="font-mono font-semibold" x-text="tenantProfile?.currency || currency"></dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-4" x-show="tenantId">
                    <label class="text-xs font-medium text-slate-500"><?php echo e(__('Project subscription')); ?></label>
                    <select name="tenant_project_subscription_id" x-model="subscriptionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                        <option value=""><?php echo e(__('— Optional —')); ?></option>
                        <template x-for="sub in subscriptions" :key="sub.id">
                            <option :value="sub.id" x-text="sub.label"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            x-show="!tenantId"
            x-cloak
        >
            <h3 class="text-sm font-semibold"><?php echo e(__('Manual client details')); ?></h3>
            <p class="text-xs text-slate-500"><?php echo e(__('Required when no tenant is selected.')); ?></p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Client name')); ?> *</label>
                    <input name="manual_client_name" x-model="clientName" value="<?php echo e(old('manual_client_name')); ?>" x-bind:required="!tenantId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Email')); ?></label>
                    <input type="email" name="manual_client_email" x-model="clientEmail" value="<?php echo e(old('manual_client_email')); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Phone')); ?></label>
                    <input name="manual_client_phone" x-model="clientPhone" value="<?php echo e(old('manual_client_phone')); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-slate-500"><?php echo e(__('Address')); ?></label>
                    <textarea name="manual_client_address" x-model="clientAddress" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950"><?php echo e(old('manual_client_address')); ?></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Document details')); ?></h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="text-xs font-medium text-slate-500"><?php echo e(__('Currency')); ?></label>
                    <input name="currency" x-model="currency" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500"><?php echo e(__('Document date')); ?></label>
                    <input type="date" name="issue_date" x-model="issueDate" value="<?php echo e(old('issue_date', now()->toDateString())); ?>" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                </div>
                <?php if($documentType !== 'receipt'): ?>
                    <div>
                        <label class="text-xs font-medium text-slate-500"><?php echo e(__('Due date')); ?></label>
                        <input type="date" name="due_date" x-model="dueDate" value="<?php echo e(old('due_date')); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500"><?php echo e(__('Amount paid (optional)')); ?></label>
                        <input type="number" step="0.01" min="0" name="amount_paid" x-model.number="amountPaid" value="<?php echo e(old('amount_paid', 0)); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($documentType === 'receipt'): ?>
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold"><?php echo e(__('Receipt payment')); ?></h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-xs text-slate-500"><?php echo e(__('Link to invoice (optional)')); ?></label>
                        <select name="linked_invoice_id" x-model="linkedInvoiceId" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                            <option value=""><?php echo e(__('Standalone receipt')); ?></option>
                            <template x-for="inv in filteredOpenInvoices()" :key="inv.id">
                                <option :value="inv.id" x-text="inv.label"></option>
                            </template>
                        </select>
                        <p x-show="tenantId && filteredOpenInvoices().length === 0" x-cloak class="mt-1 text-xs text-slate-500">
                            <?php echo e(__('No open invoices for this tenant.')); ?>

                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500"><?php echo e(__('Amount received')); ?> *</label>
                        <input type="number" step="0.01" min="0.01" name="amount_received" x-model.number="receiptAmount" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500"><?php echo e(__('Payment date')); ?> *</label>
                        <input type="date" name="payment_date" x-model="paymentDate" value="<?php echo e(old('payment_date', now()->toDateString())); ?>" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500"><?php echo e(__('Payment method')); ?> *</label>
                        <input name="payment_method" value="<?php echo e(old('payment_method', 'bank_transfer')); ?>" required class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500"><?php echo e(__('Reference')); ?></label>
                        <input name="payment_reference" value="<?php echo e(old('payment_reference')); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                    <div class="sm:col-span-2" x-show="!linkedInvoiceId">
                        <label class="text-xs text-slate-500"><?php echo e(__('Line description')); ?></label>
                        <input name="line_description" x-model="receiptLineDesc" value="<?php echo e(old('line_description', __('Payment received'))); ?>" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950">
                    </div>
                </div>
                <p class="mt-4 text-right text-sm text-slate-600 dark:text-slate-400">
                    <?php echo e(__('Receipt total (preview)')); ?>: <span class="font-mono text-base font-semibold text-emerald-700 dark:text-emerald-300" x-text="formatMoney(receiptAmount)"></span>
                </p>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <h3 class="text-sm font-semibold"><?php echo e(__('Line items')); ?></h3>
                    <button
                        type="button"
                        @click="addLine()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
                    >
                        <span aria-hidden="true">+</span> <?php echo e(__('Add line')); ?>

                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="line-items-table min-w-[720px] w-full text-left text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950/50">
                            <tr>
                                <th class="w-8 px-3 py-2">#</th>
                                <th class="px-3 py-2 min-w-[180px]"><?php echo e(__('Description')); ?></th>
                                <th class="px-3 py-2 w-28"><?php echo e(__('Type')); ?></th>
                                <th class="px-3 py-2 w-20 text-right"><?php echo e(__('Qty')); ?></th>
                                <th class="px-3 py-2 w-24 text-right"><?php echo e(__('Unit')); ?></th>
                                <th class="px-3 py-2 w-20 text-right"><?php echo e(__('Disc.')); ?></th>
                                <th class="px-3 py-2 w-16 text-right"><?php echo e(__('Tax %')); ?></th>
                                <th class="px-3 py-2 w-24 text-right"><?php echo e(__('Line')); ?></th>
                                <th class="w-24 px-3 py-2 text-right"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="(line, index) in lines" :key="line._key">
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30">
                                    <td class="px-3 py-2 text-xs text-slate-400" x-text="index + 1"></td>
                                    <td class="px-3 py-2">
                                        <input :name="'line_items['+index+'][description]'" x-model="line.description" placeholder="<?php echo e(__('Description')); ?>" required class="w-full rounded border-slate-300 text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select :name="'line_items['+index+'][item_type]'" x-model="line.item_type" class="w-full rounded border-slate-300 text-xs dark:bg-slate-950">
                                            <?php $__currentLoopData = $lineItemTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.0001" min="0" :name="'line_items['+index+'][quantity]'" x-model.number="line.quantity" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][unit_price]'" x-model.number="line.unit_price" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][discount]'" x-model.number="line.discount" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" :name="'line_items['+index+'][tax_rate]'" x-model.number="line.tax_rate" class="w-full rounded border-slate-300 text-right text-sm dark:bg-slate-950">
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-slate-700 dark:text-slate-300" x-text="formatMoney(lineTotal(line))"></td>
                                    <td class="px-3 py-2 text-right" @click.stop>
                                        <?php if (isset($component)) { $__componentOriginal110b8ff0bc0114fb450fefaa85301d27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-actions-menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-actions-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                            <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['@click' => 'duplicateLine(index)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'duplicateLine(index)']); ?><?php echo e(__('Duplicate')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                            <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['@click' => 'removeLine(index)','xBind:disabled' => 'lines.length <= 1','danger' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'removeLine(index)','x-bind:disabled' => 'lines.length <= 1','danger' => true]); ?><?php echo e(__('Remove')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $attributes = $__attributesOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__attributesOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27)): ?>
<?php $component = $__componentOriginal110b8ff0bc0114fb450fefaa85301d27; ?>
<?php unset($__componentOriginal110b8ff0bc0114fb450fefaa85301d27); ?>
<?php endif; ?>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Live total preview')); ?> <span class="font-normal normal-case">(<?php echo e(__('server calculates on save')); ?>)</span></p>
                    <dl class="ml-auto max-w-xs space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500"><?php echo e(__('Subtotal')); ?></dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().subtotal)"></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500"><?php echo e(__('Discount')); ?></dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().discount)"></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500"><?php echo e(__('Tax')); ?></dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(totals().tax)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-slate-200 pt-2 dark:border-slate-700">
                            <dt class="font-semibold text-slate-900 dark:text-white"><?php echo e(__('Total')); ?></dt>
                            <dd class="font-mono text-base font-semibold tabular-nums text-indigo-700 dark:text-indigo-300" x-text="formatMoney(totals().total)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 text-xs" x-show="amountPaid > 0">
                            <dt class="text-slate-500"><?php echo e(__('Amount paid')); ?></dt>
                            <dd class="font-mono tabular-nums text-emerald-700 dark:text-emerald-300" x-text="formatMoney(amountPaid)"></dd>
                        </div>
                        <div class="flex justify-between gap-4 text-xs" x-show="amountPaid > 0">
                            <dt class="text-slate-500"><?php echo e(__('Balance (preview)')); ?></dt>
                            <dd class="font-mono tabular-nums" x-text="formatMoney(Math.max(0, totals().total - amountPaid))"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        <?php endif; ?>

        <div>
            <label class="text-xs text-slate-500"><?php echo e(__('Notes')); ?></label>
            <textarea name="notes" x-model="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:bg-slate-950"><?php echo e(old('notes')); ?></textarea>
        </div>

        <?php if($errors->any()): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                <ul class="list-disc pl-4">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500"><?php echo e(__('Save as draft')); ?></button>
            <a href="<?php echo e(route('invoices.index')); ?>" class="rounded-xl border px-5 py-2.5 text-sm font-semibold"><?php echo e(__('Cancel')); ?></a>
        </div>
    </form>
    </div>

    <div class="manual-document-create-preview">
        <?php echo $__env->make('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'defaultTemplate' => $defaultTemplate,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'desktop',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    </div>

    <div class="manual-document-create-preview-mobile lg:hidden">
        <?php echo $__env->make('admin.invoices.partials.manual-document-preview', [
            'documentType' => $documentType,
            'defaultTemplate' => $defaultTemplate,
            'typeLabels' => $typeLabels,
            'typeBadgeVariant' => $typeBadgeVariant,
            'previewMode' => 'mobile',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    </div>

    <?php if (! $__env->hasRenderedOnce('5ec9f673-7622-4ba2-b03d-58dc5102645e')): $__env->markAsRenderedOnce('5ec9f673-7622-4ba2-b03d-58dc5102645e'); ?>
        <style>
            #manual-document-create-layout .line-items-table input[type=number]::-webkit-outer-spin-button,
            #manual-document-create-layout .line-items-table input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            #manual-document-create-layout .line-items-table input[type=number] {
                -moz-appearance: textfield;
                appearance: textfield;
            }
        </style>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/create.blade.php ENDPATH**/ ?>