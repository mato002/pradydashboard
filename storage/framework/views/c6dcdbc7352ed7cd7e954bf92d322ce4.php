<?php
    use App\Support\Billing\BillingDocumentType;

    $type = $invoice->document_type ?? BillingDocumentType::INVOICE;
    $deliveryVariant = $invoice->deliveryStatusVariant();
    $wasEmailed = $invoice->wasEmailed();
    $pdfUrl = route('invoices.pdf', $invoice);
    $isBillable = in_array($type, [BillingDocumentType::INVOICE, BillingDocumentType::PROFORMA, BillingDocumentType::QUOTATION], true);
    $regenerateBlocked = $invoice->regenerateBlockedReason();
?>

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Delivery')); ?></h3>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $deliveryVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deliveryVariant)]); ?><?php echo e($invoice->deliveryStatusLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                <?php if($invoice->finalized_at): ?>
                    <span class="text-[10px] text-slate-500"><?php echo e(__('Finalized')); ?> <?php echo e($invoice->finalized_at->diffForHumans()); ?></span>
                <?php elseif($invoice->canFinalize()): ?>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400"><?php echo e(__('Not finalized')); ?></span>
                <?php endif; ?>
            </div>
            <?php if($invoice->email_sent_at): ?>
                <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Last emailed')); ?>: <?php echo e($invoice->email_sent_at->format('M j, Y g:i A')); ?></p>
            <?php endif; ?>
            <?php if($regenerateBlocked): ?>
                <p class="mt-2 text-[10px] text-slate-500"><?php echo e($regenerateBlocked); ?></p>
            <?php endif; ?>
            <?php if($invoice->last_delivery_error): ?>
                <p class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1.5 text-xs text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                    <?php echo e($invoice->last_delivery_error); ?>

                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <?php if($invoice->canFinalize()): ?>
            <form method="post" action="<?php echo e(route('invoices.finalize', $invoice)); ?>"><?php echo csrf_field(); ?>
                <button type="submit" class="rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950 dark:text-indigo-200">
                    <?php echo e(__('Finalize')); ?>

                </button>
            </form>
        <?php endif; ?>

        <?php if($isBillable && $invoice->isDraft()): ?>
            <form method="post" action="<?php echo e(route('invoices.mark-sent', $invoice)); ?>"><?php echo csrf_field(); ?>
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                    <?php echo e(__('Finalize & mark sent')); ?>

                </button>
            </form>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal89b1c80228bf6f5d2178be42eea107b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal89b1c80228bf6f5d2178be42eea107b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.pdf-download-link','data' => ['url' => $pdfUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.pdf-download-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pdfUrl)]); ?>
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

        <?php if($invoice->canRegenerate()): ?>
            <form method="post" action="<?php echo e(route('invoices.regenerate', $invoice)); ?>" class="inline"><?php echo csrf_field(); ?>
                <button
                    type="submit"
                    class="rounded-lg border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:text-amber-200"
                    <?php if($wasEmailed): ?> onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('This document was already emailed. Regenerating creates a new revision. Continue?'))->toHtml() ?>)" <?php endif; ?>
                >
                    <?php echo e(__('Regenerate PDF')); ?>

                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if($invoice->canSend()): ?>
        <form method="post" action="<?php echo e(route('invoices.email', $invoice)); ?>" class="mt-4 space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
            <?php echo csrf_field(); ?>
            <?php if($wasEmailed): ?>
                <input type="hidden" name="resend" value="1">
            <?php endif; ?>
            <label class="block text-xs font-medium text-slate-500"><?php echo e(__('Recipient email')); ?></label>
            <div class="flex flex-wrap gap-2">
                <input
                    type="email"
                    name="recipient_email"
                    value="<?php echo e(old('recipient_email', $defaultRecipient ?? '')); ?>"
                    placeholder="<?php echo e(__('billing@client.com')); ?>"
                    class="min-w-[200px] flex-1 rounded-lg border-slate-300 text-sm dark:bg-slate-950"
                >
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">
                    <?php echo e($wasEmailed ? __('Resend email') : __('Send email')); ?>

                </button>
            </div>
            <p class="text-[10px] text-slate-500"><?php echo e(__('PDF is attached. Defaults to tenant billing email or manual client email.')); ?></p>
        </form>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/partials/delivery-actions.blade.php ENDPATH**/ ?>