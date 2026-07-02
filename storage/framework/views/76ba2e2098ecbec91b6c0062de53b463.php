<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'center',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'center',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $summary = $center['summary'] ?? [];
    $sections = $center['sections'] ?? [];
    $total = (int) ($center['total'] ?? 0);

    $chipTones = [
        'rose' => 'border-rose-200/80 bg-rose-50/80 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200',
        'amber' => 'border-amber-200/80 bg-amber-50/80 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100',
        'yellow' => 'border-yellow-200/80 bg-yellow-50/80 text-yellow-900 dark:border-yellow-900/60 dark:bg-yellow-950/30 dark:text-yellow-100',
        'sky' => 'border-sky-200/80 bg-sky-50/80 text-sky-900 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-100',
        'violet' => 'border-violet-200/80 bg-violet-50/80 text-violet-900 dark:border-violet-900/60 dark:bg-violet-950/40 dark:text-violet-100',
        'indigo' => 'border-indigo-200/80 bg-indigo-50/80 text-indigo-900 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-100',
        'slate' => 'border-slate-200/80 bg-slate-50/80 text-slate-800 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200',
    ];

    $summaryLabels = [
        'critical' => __('Critical risks'),
        'high' => __('High risks'),
        'medium' => __('Medium risks'),
        'infrastructure' => __('Infrastructure'),
        'billing' => __('Billing & collections'),
        'licensing' => __('Licensing & subscriptions'),
        'support' => __('Support escalations'),
    ];
?>

<div
    <?php echo e($attributes->merge(['class' => 'mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800/80 dark:bg-slate-900/60'])); ?>

    x-data="{
        openSections: <?php echo \Illuminate\Support\Js::from(collect($sections)->where('count', '>', 0)->pluck('id')->values()->all())->toHtml() ?>,
        toggle(id) {
            if (this.openSections.includes(id)) {
                this.openSections = this.openSections.filter(s => s !== id);
            } else {
                this.openSections.push(id);
            }
        },
        isOpen(id) { return this.openSections.includes(id); }
    }"
>
    <div class="sticky top-0 z-10 border-b border-slate-200/80 bg-white/95 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/95">
        <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-rose-600 dark:text-rose-400"><?php echo e(__('Operations Risk Center')); ?></p>
                <h2 class="mt-0.5 text-sm font-semibold tracking-tight text-slate-900 dark:text-white"><?php echo e(__('Attention required')); ?></h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <?php echo e(trans_choice(':count open risk needs review|:count open risks need review', $total, ['count' => $total])); ?>

                </p>
            </div>
            <a href="<?php echo e(route('risk-center.index')); ?>" class="inline-flex items-center gap-1 rounded-lg border border-slate-200/80 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-300 dark:hover:bg-slate-700">
                <?php echo e(__('Full Risk Center')); ?>

                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2 border-t border-slate-100/80 px-4 py-3 dark:border-slate-800/80 sm:grid-cols-4 lg:grid-cols-7">
            <?php $__currentLoopData = $summaryLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $chip = $summary[$key] ?? ['count' => 0, 'label' => __('Clear'), 'tone' => 'slate'];
                    $toneClass = $chipTones[$chip['tone']] ?? $chipTones['slate'];
                ?>
                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-xl border px-2.5 py-2', $toneClass]); ?>">
                    <p class="text-[10px] font-semibold uppercase tracking-wide opacity-80"><?php echo e($label); ?></p>
                    <p class="mt-0.5 text-xl font-bold tabular-nums"><?php echo e($chip['count']); ?></p>
                    <p class="mt-0.5 text-[10px] font-medium opacity-75"><?php echo e($chip['label']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <section>
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50/80 dark:hover:bg-slate-800/30"
                    @click="toggle(<?php echo \Illuminate\Support\Js::from($section['id'])->toHtml() ?>)"
                    :aria-expanded="isOpen(<?php echo \Illuminate\Support\Js::from($section['id'])->toHtml() ?>)"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold tabular-nums text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            :class="<?php echo \Illuminate\Support\Js::from($section['count'])->toHtml() ?> > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'"
                        ><?php echo e($section['count']); ?></span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($section['label']); ?></h3>
                            <?php if($section['count'] === 0): ?>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($section['empty']); ?></p>
                            <?php else: ?>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    <?php echo e(trans_choice(':count item|:count items', $section['count'], ['count' => $section['count']])); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="isOpen(<?php echo \Illuminate\Support\Js::from($section['id'])->toHtml() ?>) ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div
                    x-show="isOpen(<?php echo \Illuminate\Support\Js::from($section['id'])->toHtml() ?>)"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="border-t border-slate-100/80 dark:border-slate-800/80"
                >
                    <?php if($section['count'] === 0): ?>
                        <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400"><?php echo e($section['empty']); ?></p>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
                            <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($item['type'] === 'bundle'): ?>
                                    <div x-data="{ expanded: false }" class="bg-slate-50/40 dark:bg-slate-950/20">
                                        <?php if (isset($component)) { $__componentOriginal0e32131e2120500f7910e4c072858c65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e32131e2120500f7910e4c072858c65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-item-card','data' => ['severity' => $item['severity'],'severityLabel' => $item['severity_label'],'title' => $item['title'],'description' => $item['subtitle'],'timeLabel' => $item['time_label'] ?? null,'actions' => []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-item-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['severity']),'severity-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['severity_label']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['subtitle']),'time-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['time_label'] ?? null),'actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([])]); ?>
                                             <?php $__env->slot('context', null, []); ?> 
                                                <button
                                                    type="button"
                                                    @click="expanded = !expanded"
                                                    class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                                >
                                                    <span x-text="expanded ? <?php echo \Illuminate\Support\Js::from(__('Hide details'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Show affected'))->toHtml() ?>"></span>
                                                    <svg class="h-3.5 w-3.5 transition" :class="expanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                                </button>
                                                <div
                                                    x-show="expanded"
                                                    x-cloak
                                                    x-transition
                                                    class="mt-3 space-y-2"
                                                >
                                                    <?php $__currentLoopData = $item['risks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if (isset($component)) { $__componentOriginal0e32131e2120500f7910e4c072858c65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e32131e2120500f7910e4c072858c65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-item-card','data' => ['nested' => true,'severity' => $risk['severity'],'severityLabel' => $risk['severity_label'],'title' => $risk['title'],'description' => $risk['description'],'entity' => $risk['entity_label'] ?? null,'timeLabel' => $risk['time_label'] ?? null,'url' => $risk['url'] ?? null,'actions' => $risk['actions'] ?? [],'riskKey' => $risk['key']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-item-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nested' => true,'severity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['severity']),'severity-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['severity_label']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['description']),'entity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['entity_label'] ?? null),'time-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['time_label'] ?? null),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['url'] ?? null),'actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['actions'] ?? []),'risk-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['key'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $attributes = $__attributesOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__attributesOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $component = $__componentOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__componentOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                             <?php $__env->endSlot(); ?>
                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $attributes = $__attributesOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__attributesOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $component = $__componentOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__componentOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?php $risk = $item['risk']; ?>
                                    <?php if (isset($component)) { $__componentOriginal0e32131e2120500f7910e4c072858c65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e32131e2120500f7910e4c072858c65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.risk-item-card','data' => ['severity' => $risk['severity'],'severityLabel' => $risk['severity_label'],'title' => $risk['title'],'description' => $risk['description'],'entity' => $risk['entity_label'] ?? null,'timeLabel' => $risk['time_label'] ?? null,'url' => $risk['url'] ?? null,'actions' => $risk['actions'] ?? [],'riskKey' => $risk['key']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.risk-item-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['severity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['severity']),'severity-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['severity_label']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['description']),'entity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['entity_label'] ?? null),'time-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['time_label'] ?? null),'url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['url'] ?? null),'actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['actions'] ?? []),'risk-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($risk['key'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $attributes = $__attributesOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__attributesOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e32131e2120500f7910e4c072858c65)): ?>
<?php $component = $__componentOriginal0e32131e2120500f7910e4c072858c65; ?>
<?php unset($__componentOriginal0e32131e2120500f7910e4c072858c65); ?>
<?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prady-dashboard\resources\views/components/admin/operations-risk-center.blade.php ENDPATH**/ ?>