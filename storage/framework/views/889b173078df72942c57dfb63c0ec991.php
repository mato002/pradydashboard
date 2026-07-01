<?php
    $ref = $ticket;
    $priorityVariant = match ($profile['priority'] ?? 'medium') {
        'urgent', 'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        default => 'neutral',
    };
    $statusVariant = match ($profile['status'] ?? 'open') {
        'resolved', 'closed' => 'success',
        'in_progress' => 'warning',
        default => 'info',
    };
?>

<?php if (isset($component)) { $__componentOriginal895f6ef515592ffd4805667c75b9d7a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal895f6ef515592ffd4805667c75b9d7a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-layout','data' => ['heading' => $profile['id'],'subheading' => $profile['subject']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile['id']),'subheading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile['subject'])]); ?>
    <?php if (isset($component)) { $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-shell','data' => ['title' => $profile['subject'],'subtitle' => __('Tenant: :tenant', ['tenant' => $profile['tenant']]),'badge' => __('Support ticket'),'backHref' => route('support-tickets.index'),'backLabel' => __('Back to queue')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($profile['subject']),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Tenant: :tenant', ['tenant' => $profile['tenant']])),'badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Support ticket')),'back-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('support-tickets.index')),'back-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Back to queue'))]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('support-tickets.edit', $ref)); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                <?php echo e(__('Edit')); ?>

            </a>
         <?php $__env->endSlot(); ?>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-800/80">
                        <div class="flex flex-wrap items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $priorityVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priorityVariant)]); ?><?php echo e(ucfirst($profile['priority'])); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => $statusVariant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusVariant)]); ?><?php echo e(ucfirst(str_replace('_', ' ', $profile['status']))); ?> <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['variant' => 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'neutral']); ?><?php echo e($profile['category']); ?> <?php echo $__env->renderComponent(); ?>
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
                    <div class="p-5 text-sm text-slate-600 dark:text-slate-300">
                        <p><?php echo e($profile['description'] ?? __('No additional description provided.')); ?></p>
                        <?php if(! empty($profile['resolution_notes'])): ?>
                            <p class="mt-3 rounded-lg bg-emerald-50 p-3 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-100">
                                <span class="text-xs font-semibold uppercase"><?php echo e(__('Resolution')); ?></span><br>
                                <?php echo e($profile['resolution_notes']); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Timeline')); ?></h3>
                    </div>
                    <div class="divide-y divide-slate-100 p-5 dark:divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $ticket->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-3">
                                <p class="text-xs font-semibold text-slate-500">
                                    <?php echo e($msg->authorName()); ?> · <?php echo e($msg->created_at->diffForHumans()); ?>

                                    · <?php echo e($commentTypes[$msg->comment_type] ?? $msg->comment_type); ?>

                                </p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-200"><?php echo e($msg->message); ?></p>
                                <span class="text-[10px] uppercase text-slate-400"><?php echo e($visibilities[$msg->visibility] ?? $msg->visibility); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-slate-500"><?php echo e(__('No comments yet.')); ?></p>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="<?php echo e(route('support-tickets.comments.store', $ticket)); ?>" class="border-t border-slate-200/80 p-5 dark:border-slate-800/80">
                        <?php echo csrf_field(); ?>
                        <textarea name="message" rows="2" required class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="<?php echo e(__('Add a comment…')); ?>"></textarea>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <select name="comment_type" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <?php $__currentLoopData = $commentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="visibility" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <?php $__currentLoopData = $visibilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <button type="submit" class="mt-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e(__('Post')); ?></button>
                    </form>
                </div>
            </div>

            <div class="space-y-4">
                <?php if (isset($component)) { $__componentOriginal758a4d06f179e1d63015d8fd45f690dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.assigned-staff','data' => ['assignments' => $staffAssignments,'title' => __('Assigned staff')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.assigned-staff'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['assignments' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($staffAssignments),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Assigned staff'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $attributes = $__attributesOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__attributesOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd)): ?>
<?php $component = $__componentOriginal758a4d06f179e1d63015d8fd45f690dd; ?>
<?php unset($__componentOriginal758a4d06f179e1d63015d8fd45f690dd); ?>
<?php endif; ?>

                <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="font-semibold text-slate-900 dark:text-white"><?php echo e(__('Details')); ?></h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Assigned')); ?></dt><dd class="font-medium"><?php echo e($profile['assigned_to']); ?></dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Opened')); ?></dt><dd class="font-medium"><?php echo e($profile['opened_at']); ?></dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Last update')); ?></dt><dd class="font-medium"><?php echo e($profile['last_response']); ?></dd></div>
                        <?php if(! empty($profile['project'])): ?>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500"><?php echo e(__('Project')); ?></dt><dd class="font-medium"><?php echo e($profile['project']); ?></dd></div>
                        <?php endif; ?>
                        <?php if($ticket->tenant_id): ?>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500"><?php echo e(__('Tenant')); ?></dt>
                                <dd class="font-medium">
                                    <a href="<?php echo e(route('tenants.show', ['tenant' => $ticket->tenant, 'tab' => 'support', 'ticket' => $ticket->public_id])); ?>" class="text-indigo-600 hover:underline"><?php echo e($profile['tenant']); ?></a>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </div>
                </dl>
            </div>
        </div>

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
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $attributes = $__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__attributesOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d)): ?>
<?php $component = $__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d; ?>
<?php unset($__componentOriginald4dc1af139d29ac6ac9f577d65ce6a8d); ?>
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
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/support-tickets/show.blade.php ENDPATH**/ ?>