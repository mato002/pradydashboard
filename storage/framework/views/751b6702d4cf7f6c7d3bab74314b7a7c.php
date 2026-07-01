<?php
    use App\Support\Billing\BillingDocumentType;

    $currency = $invoice->currency ?? 'KES';
    $type = $invoice->document_type ?? BillingDocumentType::INVOICE;
    $isInvoice = $type === BillingDocumentType::INVOICE;
    $isQuotation = $type === BillingDocumentType::QUOTATION;
    $isProforma = $type === BillingDocumentType::PROFORMA;
    $isReceipt = $type === BillingDocumentType::RECEIPT;
    $isStatement = $type === BillingDocumentType::STATEMENT;
    $subheading = match ($type) {
        BillingDocumentType::QUOTATION => __('Quotation detail'),
        BillingDocumentType::PROFORMA => __('Proforma detail'),
        BillingDocumentType::RECEIPT => __('Receipt detail'),
        BillingDocumentType::STATEMENT => __('Statement detail'),
        default => __('Invoice detail'),
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $invoice->invoice_number,'subheading' => $subheading]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->invoice_number),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($subheading)]); ?>
    <div class="space-y-6">
        <?php if(session('status')): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <?php if($invoice->tenant): ?>
                    <a href="<?php echo e(route('tenants.show', ['tenant' => $invoice->tenant, 'tab' => 'billing'])); ?>" class="text-sm text-indigo-600 hover:underline">
                        <?php echo e($invoice->tenant->company_name); ?>

                    </a>
                <?php else: ?>
                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200"><?php echo e($invoice->clientDisplayName()); ?></span>
                    <?php if($invoice->manual_client_email): ?>
                        <span class="text-xs text-slate-500"> · <?php echo e($invoice->manual_client_email); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <?php $badge = $invoice->lifecycleBadge(); ?>
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $badge['variant']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($badge['variant'])]); ?><?php echo e($badge['label']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $invoice->statusVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->statusVariant())]); ?><?php echo e($invoice->statusLabel()); ?> <?php echo $__env->renderComponent(); ?>
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
                <p class="mt-1 text-xs text-gray-500">
                    <?php if($isStatement && $invoice->statement_period_start): ?>
                        <?php echo e(__('Period')); ?>: <?php echo e($invoice->statement_period_start->toFormattedDateString()); ?> — <?php echo e(optional($invoice->statement_period_end)->toFormattedDateString() ?? '—'); ?>

                    <?php else: ?>
                        <?php echo e(__('Due')); ?> <?php echo e(optional($invoice->due_date)->toFormattedDateString() ?? '—'); ?>

                    <?php endif; ?>
                </p>
                <?php if($isReceipt && $invoice->linkedInvoice): ?>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                        <?php echo e(__('Invoice ref')); ?>:
                        <a href="<?php echo e(route('invoices.show', $invoice->linkedInvoice)); ?>" class="text-indigo-600 hover:underline"><?php echo e($invoice->linkedInvoice->invoice_number); ?></a>
                        <?php if($invoice->linkedInvoice->isCancelled()): ?>
                            <span class="text-amber-700 dark:text-amber-300">(<?php echo e(__('source cancelled')); ?>)</span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if($invoice->convertedInvoice): ?>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                        <?php echo e(__('Converted to')); ?>:
                        <a href="<?php echo e(route('invoices.show', $invoice->convertedInvoice)); ?>" class="text-indigo-600 hover:underline"><?php echo e($invoice->convertedInvoice->invoice_number); ?></a>
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('invoices.preview', $invoice)); ?>" class="rounded-lg border px-3 py-1.5 text-xs font-semibold"><?php echo e(__('Preview document')); ?></a>
                <?php if (isset($component)) { $__componentOriginal89b1c80228bf6f5d2178be42eea107b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal89b1c80228bf6f5d2178be42eea107b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.pdf-download-link','data' => ['url' => route('invoices.pdf', $invoice)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.pdf-download-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.pdf', $invoice))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal89b1c80228bf6f5d2178be42eea107b4)): ?>
<?php $attributes = $__attributesOriginal89b1c80228bf6f5d2178be42eea107b4; ?>
<?php unset($__attributesOriginal89b1c80228bf6f5d2178be42eea107b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal89b1c80228bf6f5d2178be42eea107b4)): ?>
<?php $component = $__componentOriginal89b1c80228bf6f5d2178be42eea107b4; ?>
<?php unset($__componentOriginal89b1c80228bf6f5d2178be42eea107b4); ?>
<?php endif; ?>

                <?php if($isQuotation && $invoice->approval_status !== 'approved' && ! $invoice->isCancelled()): ?>
                    <form method="post" action="<?php echo e(route('invoices.quotations.approve', $invoice)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Approve')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($isQuotation && $invoice->canConvert()): ?>
                    <form method="post" action="<?php echo e(route('invoices.quotations.convert', $invoice)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Convert to invoice')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($isProforma && $invoice->canConvert()): ?>
                    <form method="post" action="<?php echo e(route('invoices.proforma.convert', $invoice)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Convert to invoice')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($invoice->canCancel()): ?>
                    <form method="post" action="<?php echo e(route('invoices.cancel', $invoice)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold"><?php echo e(__('Cancel')); ?></button>
                    </form>
                <?php endif; ?>

                <?php if($isInvoice && $invoice->balanceDue() <= 0.009 && $invoice->status !== 'paid'): ?>
                    <form method="post" action="<?php echo e(route('invoices.mark-paid', $invoice)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700"><?php echo e(__('Mark paid')); ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php echo $__env->make('admin.invoices.partials.delivery-actions', [
            'invoice' => $invoice,
            'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if($isInvoice && $invoice->balanceDue() > 0.009 && ! in_array($invoice->status, ['paid', 'cancelled', 'void', 'draft'])): ?>
            <?php echo $__env->make('admin.invoices.partials.collection-actions', [
                'invoice' => $invoice->loadMissing('collectionNotes'),
                'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <?php if (! ($isStatement)): ?>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                        <h3 class="text-sm font-semibold"><?php echo e($isReceipt ? __('Payment') : __('Line items')); ?></h3>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-950">
                            <tr>
                                <th class="px-4 py-2"><?php echo e(__('Description')); ?></th>
                                <th class="px-4 py-2 text-right"><?php echo e(__('Qty')); ?></th>
                                <th class="px-4 py-2 text-right"><?php echo e(__('Unit')); ?></th>
                                <th class="px-4 py-2 text-right"><?php echo e(__('Tax')); ?></th>
                                <th class="px-4 py-2 text-right"><?php echo e(__('Total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-4 py-2">
                                        <span class="text-xs uppercase text-gray-400"><?php echo e($line->item_type); ?></span>
                                        <p><?php echo e($line->description); ?></p>
                                    </td>
                                    <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format((float) $line->quantity, 2)); ?></td>
                                    <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format((float) $line->unit_price, 2)); ?></td>
                                    <td class="px-4 py-2 text-right tabular-nums"><?php echo e(number_format((float) $line->tax_amount, 2)); ?></td>
                                    <td class="px-4 py-2 text-right tabular-nums font-medium"><?php echo e(number_format((float) $line->line_total, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500"><?php echo e(__('No line items.')); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <dl class="border-t border-gray-200 px-4 py-4 text-sm dark:border-gray-800 sm:grid sm:grid-cols-2 sm:gap-2">
                        <div class="flex justify-between sm:block"><dt class="text-gray-500"><?php echo e(__('Subtotal')); ?></dt><dd class="font-medium tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format((float) $invoice->subtotal, 2)); ?></dd></div>
                        <div class="flex justify-between sm:block"><dt class="text-gray-500"><?php echo e(__('Tax')); ?></dt><dd class="font-medium tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format((float) $invoice->tax_amount, 2)); ?></dd></div>
                        <div class="flex justify-between sm:block sm:col-span-2 border-t border-dashed pt-2 dark:border-gray-700">
                            <dt class="font-semibold"><?php echo e(__('Total')); ?></dt>
                            <dd class="text-lg font-semibold tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format($invoice->invoiceTotal(), 2)); ?></dd>
                        </div>
                        <?php if($isInvoice): ?>
                            <div class="flex justify-between sm:block"><dt class="text-gray-500"><?php echo e(__('Paid')); ?></dt><dd class="tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format((float) $invoice->amount_paid, 2)); ?></dd></div>
                            <div class="flex justify-between sm:block"><dt class="text-gray-500"><?php echo e(__('Balance')); ?></dt><dd class="font-semibold tabular-nums"><?php echo e($currency); ?> <?php echo e(number_format($invoice->balanceDue(), 2)); ?></dd></div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="space-y-4">
                    <?php if($invoice->projectSubscription): ?>
                        <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                            <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Subscription')); ?></p>
                            <p class="mt-1 font-semibold"><?php echo e($invoice->projectSubscription->project?->name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($invoice->projectSubscription->package_name); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if($billingSettings->paymentInstructions()): ?>
                        <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                            <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Payment instructions')); ?></p>
                            <p class="mt-2 whitespace-pre-line text-gray-700 dark:text-gray-300"><?php echo e($billingSettings->paymentInstructions()); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if($billingSettings->invoiceFooterNotes()): ?>
                        <p class="text-xs text-gray-500 whitespace-pre-line"><?php echo e($billingSettings->invoiceFooterNotes()); ?></p>
                    <?php endif; ?>

                    <?php if($invoice->canRecordPayment()): ?>
                        <?php echo $__env->make('admin.invoices.partials.record-payment-form', [
                            'formAction' => route('invoices.payments.store', $invoice),
                            'defaultInvoiceId' => $invoice->id,
                            'defaultTenantId' => $invoice->tenant_id,
                            'paymentSources' => \App\Support\Billing\PaymentSource::all(),
                            'compact' => true,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>

                    <?php if($isInvoice && $invoice->payments->isNotEmpty()): ?>
                        <div class="rounded-xl border border-gray-200 p-4 text-sm dark:border-gray-800">
                            <p class="text-xs font-medium uppercase text-gray-500"><?php echo e(__('Payments')); ?></p>
                            <ul class="mt-2 space-y-2">
                                <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="text-xs">
                                        <?php echo e(optional($pay->paid_at)->toFormattedDateString()); ?> — <?php echo e($currency); ?> <?php echo e(number_format((float) $pay->amount, 2)); ?>

                                        <span class="text-gray-500">(<?php echo e($pay->method); ?>)</span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalc535bf0441c81dd81939b35e9ab2587f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc535bf0441c81dd81939b35e9ab2587f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.activity-feed','data' => ['logs' => $activityLogs,'class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.activity-feed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activityLogs),'class' => 'mt-6']); ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/show.blade.php ENDPATH**/ ?>