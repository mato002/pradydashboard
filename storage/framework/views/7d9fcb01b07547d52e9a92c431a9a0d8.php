<?php
    $isMobilePreview = ($previewMode ?? 'desktop') === 'mobile';
?>

<?php if(! $isMobilePreview): ?>
    <?php if (! $__env->hasRenderedOnce('c4667796-0779-41f9-8fff-72fe00f7c071')): $__env->markAsRenderedOnce('c4667796-0779-41f9-8fff-72fe00f7c071'); ?>
        <?php echo $__env->make('admin.invoices.partials.manual-document-preview-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php endif; ?>

<aside
    <?php if(! $isMobilePreview): ?> id="manual-document-preview" <?php endif; ?>
    data-testid="manual-document-preview"
    data-preview-mode="<?php echo e($previewMode ?? 'desktop'); ?>"
    data-initial-doc-type="<?php echo e($documentType); ?>"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'manual-doc-preview-panel flex w-full max-w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80 dark:border-slate-700 dark:bg-slate-900/60',
        'sticky top-24 max-h-[calc(100vh-7rem)]' => ! $isMobilePreview,
        'fixed inset-y-0 right-0 z-50 hidden max-w-md shadow-2xl' => $isMobilePreview,
    ]); ?>"
    <?php if($isMobilePreview): ?>
        x-show="previewOpen"
        x-cloak
        :class="{ '!flex': previewOpen }"
    <?php endif; ?>
>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200" x-text="documentTypeBadge()"><?php echo e($typeLabels[$documentType] ?? $documentType); ?></span>
            <?php if($defaultTemplate ?? null): ?>
                <span class="text-xs font-medium text-slate-600 dark:text-slate-300"><?php echo e($defaultTemplate->name); ?></span>
                <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-500 ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-700"><?php echo e(strtoupper($defaultTemplate->paper_size)); ?></span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-1">
            <button type="button" @click="previewZoom = Math.max(0.6, previewZoom - 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="<?php echo e(__('Zoom out')); ?>">−</button>
            <span class="min-w-[3rem] text-center text-[10px] text-slate-500" x-text="Math.round(previewZoom * 100) + '%'">100%</span>
            <button type="button" @click="previewZoom = Math.min(1.2, previewZoom + 0.1)" class="rounded border px-2 py-0.5 text-xs text-slate-600 hover:bg-white dark:hover:bg-slate-800" title="<?php echo e(__('Zoom in')); ?>">+</button>
            <button type="button" @click="previewOpen = false" class="lg:hidden rounded border px-2 py-0.5 text-xs text-slate-600"><?php echo e(__('Close')); ?></button>
        </div>
    </div>

    <p class="px-4 pt-2 text-[10px] text-slate-500 dark:text-slate-400">
        <?php echo e(__('Preview only — final totals calculated on save')); ?>

    </p>

    <div class="manual-doc-preview-viewport flex-1 overflow-x-hidden overflow-y-auto p-3">
        <div
            class="manual-doc-preview-scaler mx-auto w-full max-w-full"
            :style="'transform: scale(' + previewZoom + '); transform-origin: top center;'"
        >
            <div
                class="manual-doc-preview-frame"
                data-testid="manual-document-preview-frame"
                :class="previewPaperSize === 'A5' ? 'is-a5' : 'is-a4'"
            >
                <iframe
                    x-ref="previewFrame"
                    :srcdoc="previewHtml"
                    sandbox="allow-same-origin"
                    class="manual-doc-preview-iframe"
                    :title="documentPreviewTitle()"
                ></iframe>
            </div>
        </div>
    </div>
</aside>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/partials/manual-document-preview.blade.php ENDPATH**/ ?>