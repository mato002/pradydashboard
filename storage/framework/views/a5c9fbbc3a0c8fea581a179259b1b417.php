<div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e(__('Add provider notice')); ?></h3>
    <form method="post" action="<?php echo e(route('servers.notices.store', $server)); ?>" class="mt-4 grid gap-4 sm:grid-cols-2">
        <?php echo csrf_field(); ?>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Source / provider')); ?></label>
            <input type="text" name="source" value="<?php echo e(old('source', $server->provider)); ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Type')); ?></label>
            <select name="notice_type" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <?php $__currentLoopData = \App\Models\ServerProviderNotice::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php if(old('notice_type') === $type): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', ucfirst($type))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Title')); ?></label>
            <input type="text" name="title" value="<?php echo e(old('title')); ?>" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Message')); ?></label>
            <textarea name="body" rows="2" class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"><?php echo e(old('body')); ?></textarea>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Severity')); ?></label>
            <select name="severity" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <?php $__currentLoopData = \App\Models\ServerProviderNotice::SEVERITIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($sev); ?>"><?php echo e(ucfirst($sev)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Status')); ?></label>
            <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <?php $__currentLoopData = \App\Models\ServerProviderNotice::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($st); ?>" <?php if($st === 'open'): echo 'selected'; endif; ?>><?php echo e(ucfirst($st)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Notice date')); ?></label>
            <input type="date" name="notice_date" value="<?php echo e(old('notice_date', now()->toDateString())); ?>" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Due date')); ?></label>
            <input type="date" name="due_date" value="<?php echo e(old('due_date')); ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Email / reference')); ?></label>
            <input type="text" name="source_reference" value="<?php echo e(old('source_reference')); ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?php echo e(__('Attachment ref')); ?></label>
            <input type="text" name="attachment_reference" value="<?php echo e(old('attachment_reference')); ?>" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><?php echo e(__('Save notice')); ?></button>
        </div>
    </form>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/admin/servers/partials/show/notice-form.blade.php ENDPATH**/ ?>