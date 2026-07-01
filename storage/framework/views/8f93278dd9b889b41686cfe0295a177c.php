<div
    x-show="paletteOpen"
    x-cloak
    class="fixed inset-0 z-[80] flex items-start justify-center px-4 pt-[12vh] sm:px-6"
    role="presentation"
>
    <div
        class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]"
        @click="closePalette()"
        aria-hidden="true"
    ></div>

    <div
        class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
        role="dialog"
        aria-modal="true"
        aria-labelledby="prady-command-palette-title"
        @click.stop
    >
        <h2 id="prady-command-palette-title" class="sr-only"><?php echo e(__('Global feature finder')); ?></h2>

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <div class="relative">
                <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                <input
                    id="prady-command-palette-input"
                    type="search"
                    x-ref="paletteInput"
                    :value="paletteQuery"
                    @input="onPaletteQueryInput($event)"
                    @keydown.arrow-down.prevent="movePaletteSelection(1)"
                    @keydown.arrow-up.prevent="movePaletteSelection(-1)"
                    @keydown.enter.prevent="openPaletteSelection()"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-24 text-sm text-slate-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    placeholder="<?php echo e(__('Search tenants, invoices, servers, settings, features…')); ?>"
                    autocomplete="off"
                    aria-label="<?php echo e(__('Search dashboard features')); ?>"
                >
                <div class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 items-center gap-1 sm:flex">
                    <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:border-slate-600 dark:bg-slate-800">Ctrl</kbd>
                    <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-500 dark:border-slate-600 dark:bg-slate-800">K</kbd>
                </div>
            </div>
        </div>

        <div class="max-h-[min(60vh,28rem)] overflow-y-auto py-2" x-ref="paletteResultsPanel">
            <template x-if="paletteQuery.trim() === '' && recentItems.length > 0">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Recent')); ?></p>
                    <template x-for="(item, index) in recentItems" :key="`recent-${item.id}`">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === index ? 'bg-indigo-50 dark:bg-indigo-950/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                            @mouseenter="paletteHighlightIndex = index"
                        >
                            <button type="button" class="min-w-0 flex-1 text-left" @click="navigatePaletteItem(item)">
                                <span class="block text-sm font-medium text-slate-900 dark:text-white" x-text="item.label"></span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                            </button>
                            <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="openPaletteItemNewTab(item)" title="<?php echo e(__('Open in new tab')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'up-right-from-square','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'up-right-from-square','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                                </button>
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="copyPaletteItemLink(item)" title="<?php echo e(__('Copy link')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'clipboard','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                                </button>
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="toggleDiscoveryFavorite(item.id)" title="<?php echo e(__('Favorite')); ?>">
                                    <span x-text="isDiscoveryFavorite(item.id) ? '★' : '☆'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>
            </template>

            <template x-if="paletteQuery.trim() === '' && favoriteDiscoveryItems.length > 0">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Favorites')); ?></p>
                    <template x-for="(item, index) in favoriteDiscoveryItems" :key="`fav-${item.id}`">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === (recentItems.length + index) ? 'bg-indigo-50 dark:bg-indigo-950/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                            @mouseenter="paletteHighlightIndex = recentItems.length + index"
                        >
                            <button type="button" class="min-w-0 flex-1 text-left" @click="navigatePaletteItem(item)">
                                <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                    <span class="text-amber-500">★</span>
                                    <span x-text="item.label"></span>
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                            </button>
                        </div>
                    </template>
                </section>
            </template>

            <template x-if="paletteQueryIsActive() && paletteLoading">
                <p class="px-4 py-8 text-center text-sm text-slate-500"><?php echo e(__('Searching…')); ?></p>
            </template>

            <template x-if="paletteQueryIsActive() && ! paletteLoading && paletteSections.length > 0">
                <div>
            <template x-for="section in paletteSections" :key="section.key">
                <section class="px-2 pb-2">
                    <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500" x-text="section.label"></p>
                    <template x-for="(item, index) in section.items" :key="item.id">
                        <div
                            class="group flex items-start gap-2 rounded-lg px-2 py-2"
                            :class="paletteHighlightIndex === paletteSectionOffset(section.key, index) ? 'bg-indigo-50 dark:bg-indigo-950/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                            @mouseenter="paletteHighlightIndex = paletteSectionOffset(section.key, index)"
                        >
                            <button type="button" class="min-w-0 flex-1 text-left" @click="navigatePaletteItem(item)">
                                <span class="block text-sm font-medium text-slate-900 dark:text-white" x-text="item.label"></span>
                                <span class="mt-0.5 block text-xs text-slate-500" x-text="item.path"></span>
                                <span x-show="item.description" class="mt-1 block text-xs text-slate-400" x-text="item.description"></span>
                            </button>
                            <div class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="openPaletteItemNewTab(item)" title="<?php echo e(__('Open in new tab')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'up-right-from-square','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'up-right-from-square','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                                </button>
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="copyPaletteItemLink(item)" title="<?php echo e(__('Copy link')); ?>">
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'clipboard','class' => 'h-3.5 w-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clipboard','class' => 'h-3.5 w-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $attributes = $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__attributesOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a)): ?>
<?php $component = $__componentOriginal56804098dcf376a0e2227cb77b6cd00a; ?>
<?php unset($__componentOriginal56804098dcf376a0e2227cb77b6cd00a); ?>
<?php endif; ?>
                                </button>
                                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="toggleDiscoveryFavorite(item.id)" title="<?php echo e(__('Favorite')); ?>">
                                    <span x-text="isDiscoveryFavorite(item.id) ? '★' : '☆'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>
            </template>
                </div>
            </template>

            <p
                x-show="paletteQueryIsActive() && ! paletteLoading && paletteSearchError"
                class="px-4 py-8 text-center text-sm text-amber-600 dark:text-amber-400"
            >
                <?php echo e(__('Search is temporarily unavailable. Refresh the page and try again.')); ?>

            </p>

            <p
                x-show="paletteQueryIsActive() && ! paletteLoading && paletteSearchSettled && ! paletteSearchError && paletteFlatResults.length === 0"
                class="px-4 py-8 text-center text-sm text-slate-500"
            >
                <?php echo e(__('No features found. Try a different keyword.')); ?>

            </p>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50/80 px-4 py-2 text-[11px] text-slate-500 dark:border-slate-700 dark:bg-slate-950/60">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 px-1 dark:border-slate-600">↑</kbd><kbd class="rounded border border-slate-200 px-1 dark:border-slate-600">↓</kbd> <?php echo e(__('Navigate')); ?></span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 px-1 dark:border-slate-600">↵</kbd> <?php echo e(__('Open')); ?></span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 px-1 dark:border-slate-600">Esc</kbd> <?php echo e(__('Close')); ?></span>
            </div>
            <span><?php echo e(__('Feature search — not record search')); ?></span>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/command-palette.blade.php ENDPATH**/ ?>