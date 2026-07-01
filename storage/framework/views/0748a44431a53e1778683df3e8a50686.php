<?php
    $trendMax = max(collect($invoiceTrend ?? [])->max(fn ($p) => max($p['issued'], $p['paid'])) ?? 0, 1);
    $revenueMax = max(collect($revenueSeries ?? [])->max('value') ?? 0, 1);
?>
<form method="get" action="<?php echo e(route('invoices.index')); ?>" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 dark:border-slate-800 dark:bg-slate-950/40">
    <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('Tenant')); ?></label>
        <select name="tenant_id" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
            <option value=""><?php echo e(__('All')); ?></option>
            <?php $__currentLoopData = $filterTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t->id); ?>" <?php if(request('tenant_id') == $t->id): echo 'selected'; endif; ?>><?php echo e($t->company_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('From')); ?></label>
        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
    </div>
    <div>
        <label class="text-[10px] font-semibold uppercase text-slate-500"><?php echo e(__('To')); ?></label>
        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="mt-1 block rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
    </div>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="overdue" value="1" <?php if(request()->boolean('overdue')): echo 'checked'; endif; ?>> <?php echo e(__('Overdue')); ?></label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="unpaid" value="1" <?php if(request()->boolean('unpaid')): echo 'checked'; endif; ?>> <?php echo e(__('Unpaid')); ?></label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="paid" value="1" <?php if(request()->boolean('paid')): echo 'checked'; endif; ?>> <?php echo e(__('Paid')); ?></label>
    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="draft" value="1" <?php if(request()->boolean('draft')): echo 'checked'; endif; ?>> <?php echo e(__('Draft')); ?></label>
    <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white dark:bg-slate-200 dark:text-slate-900"><?php echo e(__('Filter')); ?></button>
</form>

<?php if (isset($component)) { $__componentOriginal80e3cfb6c308fc466397e893a1918940 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80e3cfb6c308fc466397e893a1918940 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table-panel','data' => ['title' => __('Document register')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Document register'))]); ?>
    <table class="prady-table">
        <thead>
            <tr>
                <th><?php echo e(__('No.')); ?></th>
                <th><?php echo e(__('Tenant')); ?></th>
                <th><?php echo e(__('Project')); ?></th>
                <th><?php echo e(__('Type')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
                <th class="text-right"><?php echo e(__('Total')); ?></th>
                <th class="text-right"><?php echo e(__('Paid')); ?></th>
                <th class="text-right"><?php echo e(__('Balance')); ?></th>
                <th><?php echo e(__('Due')); ?></th>
                <th><?php echo e(__('Delivery')); ?></th>
                <th><?php echo e(__('By')); ?></th>
                <th><?php echo e(__('Aging')); ?></th>
                <th class="text-right"><?php echo e(__('Actions')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['bg-rose-50/50 dark:bg-rose-950/20' => $invoice->status === 'overdue']); ?>">
                    <td>
                        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="font-mono text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"><?php echo e($invoice->invoice_number); ?></a>
                    </td>
                    <td class="text-sm">
                        <?php if($invoice->tenant): ?>
                            <?php echo e($invoice->tenant->company_name); ?>

                        <?php else: ?>
                            <span class="font-medium"><?php echo e($invoice->manual_client_name ?? '—'); ?></span>
                            <span class="mt-0.5 block text-[10px] font-medium text-amber-600 dark:text-amber-400"><?php echo e(__('Manual')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="max-w-[100px] truncate text-xs"><?php echo e($invoice->projectSubscription?->project?->name ?? $invoice->product_name ?? '—'); ?></td>
                    <td>
                        <?php
                            $docVariant = match ($invoice->document_type) {
                                'quotation' => 'purple',
                                'proforma' => 'warning',
                                'receipt' => 'success',
                                'invoice' => 'info',
                                default => 'neutral',
                            };
                        ?>
                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $docVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($docVariant)]); ?><?php echo e($invoice->documentTypeLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    </td>
                    <td>
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
                        <?php if($invoice->lifecycleLabel()): ?>
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $invoice->lifecycleVariant(),'class' => 'ml-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->lifecycleVariant()),'class' => 'ml-1']); ?><?php echo e($invoice->lifecycleLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-right font-mono text-xs tabular-nums"><?php echo e($invoice->formattedAmount()); ?></td>
                    <td class="text-right font-mono text-xs tabular-nums"><?php echo e(\App\Models\TenantInvoice::formatMoney((float) $invoice->amount_paid, $invoice->currency)); ?></td>
                    <td class="text-right font-mono text-xs tabular-nums"><?php echo e($invoice->formattedBalance()); ?></td>
                    <td class="text-xs"><?php echo e($invoice->due_date?->format('M j, Y') ?? '—'); ?></td>
                    <td>
                        <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $invoice->deliveryStatusVariant()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice->deliveryStatusVariant())]); ?><?php echo e($invoice->deliveryStatusLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
                    </td>
                    <td class="max-w-[80px] truncate text-xs text-slate-500" title="<?php echo e($invoice->generated_by); ?>"><?php echo e($invoice->generated_by ?? '—'); ?></td>
                    <td class="text-xs font-medium <?php echo e($invoice->agingColor()); ?>"><?php echo e($invoice->agingLabel()); ?></td>
                    <td class="text-right" @click.stop>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.preview', $invoice),'fullNav' => true,'target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.preview', $invoice)),'fullNav' => true,'target' => '_blank']); ?><?php echo e(__('Preview')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $attributes = $__attributesOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__attributesOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal813c425cf2d1bd61de120181fddff91e)): ?>
<?php $component = $__componentOriginal813c425cf2d1bd61de120181fddff91e; ?>
<?php unset($__componentOriginal813c425cf2d1bd61de120181fddff91e); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal89b1c80228bf6f5d2178be42eea107b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal89b1c80228bf6f5d2178be42eea107b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.pdf-download-link','data' => ['url' => route('invoices.pdf', $invoice),'variant' => 'menu','label' => __('Download PDF')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.pdf-download-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.pdf', $invoice)),'variant' => 'menu','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Download PDF'))]); ?>
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
                            <?php if (isset($component)) { $__componentOriginal813c425cf2d1bd61de120181fddff91e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal813c425cf2d1bd61de120181fddff91e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.email', $invoice),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.email', $invoice)),'method' => 'POST']); ?><?php echo e(__('Email')); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.row-action','data' => ['href' => route('invoices.show', $invoice)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.row-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('invoices.show', $invoice))]); ?><?php echo e(__('Open')); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="13" class="py-12 text-center text-sm text-slate-500"><?php echo e(__('No documents match your filters.')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
     <?php $__env->slot('footer', null, []); ?> <?php echo e($invoices->links()); ?> <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $attributes = $__attributesOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__attributesOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80e3cfb6c308fc466397e893a1918940)): ?>
<?php $component = $__componentOriginal80e3cfb6c308fc466397e893a1918940; ?>
<?php unset($__componentOriginal80e3cfb6c308fc466397e893a1918940); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/invoices/partials/register-table.blade.php ENDPATH**/ ?>