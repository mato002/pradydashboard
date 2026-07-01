<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('Pay subscription')); ?> — <?php echo e($tenant->company_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="rounded-2xl border border-indigo-500/30 bg-slate-900/90 p-8 shadow-2xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400"><?php echo e(__('PradytecAI billing')); ?></p>
            <h1 class="mt-2 text-2xl font-semibold"><?php echo e(__('Complete your payment')); ?></h1>
            <p class="mt-2 text-sm text-slate-400"><?php echo e($tenant->company_name); ?></p>

            <?php if($billing && ($billing['amount_due'] ?? 0) > 0): ?>
                <div class="mt-6 rounded-xl bg-slate-800/60 p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e(__('Amount due')); ?></p>
                    <p class="mt-1 text-3xl font-bold tabular-nums text-white"><?php echo e($billing['amount_due_formatted']); ?></p>
                    <?php if(! empty($billing['invoice_number'])): ?>
                        <p class="mt-2 text-sm text-slate-400"><?php echo e(__('Invoice')); ?>: <span class="font-mono font-semibold text-slate-200"><?php echo e($billing['invoice_number']); ?></span></p>
                    <?php endif; ?>
                    <?php if(! empty($billing['due_date'])): ?>
                        <p class="text-sm text-slate-400"><?php echo e(__('Due')); ?>: <?php echo e($billing['due_date']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($billing && ! empty($billing['payment_instructions'])): ?>
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-slate-200"><?php echo e(__('How to pay')); ?></h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300"><?php echo e($billing['payment_instructions']); ?></p>
                </div>
            <?php endif; ?>

            <?php if($invoices->isNotEmpty()): ?>
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-slate-200"><?php echo e(__('Open invoices')); ?></h2>
                    <ul class="mt-2 divide-y divide-slate-700/80 rounded-xl border border-slate-700/80">
                        <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between px-4 py-3 text-sm">
                                <span class="font-mono"><?php echo e($invoice->invoice_number); ?></span>
                                <span class="font-semibold tabular-nums"><?php echo e($invoice->formattedBalance()); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(! $billing): ?>
                <p class="mt-6 text-sm text-slate-400"><?php echo e(__('No outstanding balance on file. Contact billing if you believe this is an error.')); ?></p>
            <?php endif; ?>

            <div class="mt-8 flex flex-wrap gap-3">
                <?php if($billing && ! empty($billing['billing_phone'])): ?>
                    <a href="tel:<?php echo e(preg_replace('/\s+/', '', $billing['billing_phone'])); ?>" class="inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                        <?php echo e(__('Call')); ?> <?php echo e($billing['billing_phone']); ?>

                    </a>
                <?php endif; ?>
                <?php if($billing && ! empty($billing['billing_email'])): ?>
                    <a href="mailto:<?php echo e($billing['billing_email']); ?>" class="inline-flex items-center rounded-xl border border-slate-600 px-5 py-2.5 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                        <?php echo e(__('Email billing')); ?>

                    </a>
                <?php endif; ?>
            </div>

            <p class="mt-8 text-xs text-slate-500">
                <?php echo e(__('After payment is recorded in our system, your hosted application access restores automatically within a few minutes.')); ?>

            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/public/billing/pay.blade.php ENDPATH**/ ?>