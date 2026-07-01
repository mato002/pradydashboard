<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $invoice->invoice_number,'subheading' => __('Document preview')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->invoice_number),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Document preview'))]); ?>
    <?php if(session('status')): ?>
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="text-sm text-indigo-600 hover:underline"><?php echo e(__('Back to invoice')); ?></a>
        <form method="post" action="<?php echo e(route('invoices.regenerate', $invoice)); ?>" class="inline flex items-center gap-2"><?php echo csrf_field(); ?>
            <?php if($invoice->canRegenerate()): ?>
                <button
                    type="submit"
                    class="rounded-lg border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:text-amber-200"
                    <?php if($invoice->wasEmailed()): ?> onclick="return confirm(<?php echo \Illuminate\Support\Js::from(__('This document was already emailed. Regenerating creates a new revision. Continue?'))->toHtml() ?>)" <?php endif; ?>
                >
                    <?php echo e(__('Save PDF snapshot')); ?>

                </button>
            <?php else: ?>
                <span class="text-xs text-slate-500"><?php echo e($invoice->regenerateBlockedReason()); ?></span>
            <?php endif; ?>
        </form>
    </div>

    <div
        id="invoice-preview-layout"
        data-testid="invoice-preview-layout"
        class="invoice-document-split-layout"
    >
        <div class="invoice-document-split-main space-y-4">
            <?php echo $__env->make('admin.invoices.partials.delivery-actions', [
                'invoice' => $invoice,
                'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if($invoice->balanceDue() > 0.009 && ! in_array($invoice->status, ['paid', 'cancelled', 'void', 'draft'])): ?>
                <?php echo $__env->make('admin.invoices.partials.collection-actions', [
                    'invoice' => $invoice->loadMissing('collectionNotes'),
                    'defaultRecipient' => $defaultRecipient ?? $invoice->defaultRecipientEmail(),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <div class="invoice-document-split-preview-mobile lg:hidden">
                <p class="mb-2 text-xs font-medium text-slate-500"><?php echo e(__('Document preview')); ?></p>
                <?php if (isset($component)) { $__componentOriginal32e5068895aba8f4d9840d76697c36e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32e5068895aba8f4d9840d76697c36e4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.document-preview-frame','data' => ['html' => $previewHtml,'paperSize' => $documentTemplate->paper_size,'title' => __('Preview')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.document-preview-frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['html' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($previewHtml),'paper-size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTemplate->paper_size),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Preview'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32e5068895aba8f4d9840d76697c36e4)): ?>
<?php $attributes = $__attributesOriginal32e5068895aba8f4d9840d76697c36e4; ?>
<?php unset($__attributesOriginal32e5068895aba8f4d9840d76697c36e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32e5068895aba8f4d9840d76697c36e4)): ?>
<?php $component = $__componentOriginal32e5068895aba8f4d9840d76697c36e4; ?>
<?php unset($__componentOriginal32e5068895aba8f4d9840d76697c36e4); ?>
<?php endif; ?>
            </div>
        </div>

        <div class="invoice-document-split-preview">
            <div class="invoice-document-split-preview-inner">
                <p class="mb-2 hidden text-xs font-medium text-slate-500 lg:block"><?php echo e(__('Document preview')); ?></p>
                <?php if (isset($component)) { $__componentOriginal32e5068895aba8f4d9840d76697c36e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32e5068895aba8f4d9840d76697c36e4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.document-preview-frame','data' => ['html' => $previewHtml,'paperSize' => $documentTemplate->paper_size,'title' => __('Preview')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.document-preview-frame'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['html' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($previewHtml),'paper-size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTemplate->paper_size),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Preview'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32e5068895aba8f4d9840d76697c36e4)): ?>
<?php $attributes = $__attributesOriginal32e5068895aba8f4d9840d76697c36e4; ?>
<?php unset($__attributesOriginal32e5068895aba8f4d9840d76697c36e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32e5068895aba8f4d9840d76697c36e4)): ?>
<?php $component = $__componentOriginal32e5068895aba8f4d9840d76697c36e4; ?>
<?php unset($__componentOriginal32e5068895aba8f4d9840d76697c36e4); ?>
<?php endif; ?>
            </div>
        </div>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/preview.blade.php ENDPATH**/ ?>