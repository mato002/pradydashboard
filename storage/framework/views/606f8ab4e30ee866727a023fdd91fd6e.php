<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'heading' => null,
    'subheading' => null,
    'headerSlot' => null,
    'documentTitle' => null,
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
    'heading' => null,
    'subheading' => null,
    'headerSlot' => null,
    'documentTitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\Discovery\FeatureRegistry;

    $docTitle = $documentTitle
        ?? ($heading ? $heading.' — '.config('app.name', 'Prady Dashboard') : config('app.name', 'Prady Dashboard'));

    $featureDiscoveryCatalog = auth()->check()
        ? app(FeatureRegistry::class)->indexForClient()
        : [];

    $featureDiscoveryPayload = [
        'searchUrl' => route('feature-discovery.search', [], false),
        'catalog' => $featureDiscoveryCatalog,
    ];
?>

<!DOCTYPE html>
<html class="h-full" lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo e($docTitle); ?></title>
        <script>
            (function () {
                try {
                    var t = localStorage.getItem('prady-theme') || 'light';
                    var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php if(auth()->guard()->check()): ?>
            <script>
                window.__pradyFeatureDiscovery = <?php echo json_encode($featureDiscoveryPayload, 15, 512) ?>;
            </script>
        <?php endif; ?>
    </head>
    <body class="h-full font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div
            x-data="pradyShell()"
            class="relative min-h-full"
            <?php if(auth()->guard()->check()): ?>
                data-feature-discovery-url="<?php echo e(route('feature-discovery.search', [], false)); ?>"
            <?php endif; ?>
            @keydown.escape.window="if (paletteOpen) closePalette()"
            @keydown.ctrl.k.window.prevent="openPalette()"
            @keydown.meta.k.window.prevent="openPalette()"
        >
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:!hidden"
                @click="sidebarOpen = false"
                @keydown.escape.window="sidebarOpen = false"
                aria-hidden="true"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex min-h-screen flex-col border-r border-sidebar-border bg-sidebar text-slate-300 shadow-2xl transition-all duration-300 ease-out lg:z-30"
                :class="[
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    $store.sidebar.collapsed ? 'lg:w-20' : 'lg:w-64',
                    'w-64',
                ]"
            >
                <div class="flex h-[4.25rem] shrink-0 items-center gap-3 border-b border-sidebar-border px-4">
                    <a href="<?php echo e(route('dashboard')); ?>" data-prady-nav class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold tracking-tight text-white shadow-lg shadow-indigo-500/30">P</span>
                        <div class="min-w-0 flex-1 overflow-hidden transition-opacity" :class="$store.sidebar.collapsed ? 'lg:opacity-0 lg:pointer-events-none' : ''">
                            <p class="truncate text-sm font-semibold tracking-tight text-white">Prady Dashboard</p>
                            <p class="truncate text-[11px] text-slate-500"><?php echo e(__('Cloud operations')); ?></p>
                        </div>
                    </a>
                </div>

                <div class="prady-scrollbar flex-1 overflow-y-auto py-3">
                    <?php echo $__env->make('admin.partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="shrink-0 border-t border-sidebar-border p-3">
                    <a
                        href="https://laravel.com/docs"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white"
                        :class="$store.sidebar.collapsed ? 'lg:justify-center' : ''"
                    >
                        <svg class="h-5 w-5 shrink-0 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="truncate" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"><?php echo e(__('Documentation')); ?></span>
                    </a>
                    <button
                        type="button"
                        class="mt-2 hidden w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 lg:flex"
                        @click="$store.sidebar.toggleCollapsed()"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <span x-show="!$store.sidebar.collapsed" x-transition><?php echo e(__('Collapse')); ?></span>
                    </button>
                </div>
            </aside>

            <div class="min-h-screen pl-0 transition-[padding] duration-300 ease-out" :class="$store.sidebar.collapsed ? 'lg:pl-20' : 'lg:pl-64'">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/75">
                    <div class="flex h-[4.25rem] items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-white p-2 text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 lg:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click="sidebarOpen = ! sidebarOpen"
                        >
                            <span class="sr-only"><?php echo e(__('Toggle sidebar')); ?></span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5h16.5" />
                            </svg>
                        </button>

                        <div class="hidden min-w-0 shrink sm:block md:max-w-[9rem] lg:max-w-xs xl:max-w-sm">
                            <?php if(isset($headerSlot) && trim((string) $headerSlot) !== ''): ?>
                                <div class="min-w-0 text-slate-900 dark:text-white [&_h1]:truncate [&_h1]:text-base [&_h1]:font-semibold [&_h1]:tracking-tight [&_h2]:truncate [&_h2]:text-base [&_h2]:font-semibold [&_h2]:tracking-tight sm:[&_h1]:text-lg sm:[&_h2]:text-lg">
                                    <?php echo $headerSlot; ?>

                                </div>
                            <?php elseif($heading): ?>
                                <h1 id="prady-page-heading" class="truncate text-base font-semibold tracking-tight text-slate-900 dark:text-white sm:text-lg"><?php echo e($heading); ?></h1>
                            <?php endif; ?>
                            <p id="prady-page-subheading" class="truncate text-xs text-slate-500 dark:text-slate-400">
                                <?php if(auth()->guard()->check()): ?>
                                    <?php echo e($subheading ?? __('Welcome back')); ?>, <?php echo e(Auth::user()->name); ?>

                                <?php else: ?>
                                    <?php echo e($subheading ?? config('app.name', 'Prady Dashboard')); ?>

                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if(auth()->guard()->check()): ?>
                            <div class="hidden min-w-0 flex-1 md:block">
                                <button
                                    type="button"
                                    class="relative flex w-full min-w-0 max-w-xl items-center rounded-xl border border-slate-200/80 bg-slate-50/80 py-2 pl-9 pr-14 text-left text-sm text-slate-500 transition hover:border-indigo-300 hover:bg-white lg:mx-auto lg:max-w-2xl xl:pr-16 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-400 dark:hover:border-indigo-700 dark:hover:bg-slate-900"
                                    @click="openPalette()"
                                    aria-label="<?php echo e(__('Open feature finder')); ?>"
                                >
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
                                    <span class="truncate lg:hidden"><?php echo e(__('Search features…')); ?></span>
                                    <span class="hidden truncate lg:inline"><?php echo e(__('Search tenants, invoices, servers, settings, features…')); ?></span>
                                    <span class="pointer-events-none absolute right-2 top-1/2 hidden -translate-y-1/2 items-center gap-1 xl:flex">
                                        <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-400 dark:border-slate-600 dark:bg-slate-800">Ctrl</kbd>
                                        <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-400 dark:border-slate-600 dark:bg-slate-800">K</kbd>
                                    </span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="ml-auto flex items-center gap-1 sm:gap-2">
                            <?php if(auth()->guard()->check()): ?>
                                <button
                                    type="button"
                                    class="inline-flex rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 md:hidden dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                    @click="openPalette()"
                                    aria-label="<?php echo e(__('Open feature finder')); ?>"
                                >
                                    <?php if (isset($component)) { $__componentOriginal56804098dcf376a0e2227cb77b6cd00a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56804098dcf376a0e2227cb77b6cd00a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.icon','data' => ['name' => 'magnifying-glass','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'magnifying-glass','class' => 'h-5 w-5']); ?>
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
                            <?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc857eac6ac27a8a288fcf40383760843 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc857eac6ac27a8a288fcf40383760843 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.role-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('role-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc857eac6ac27a8a288fcf40383760843)): ?>
<?php $attributes = $__attributesOriginalc857eac6ac27a8a288fcf40383760843; ?>
<?php unset($__attributesOriginalc857eac6ac27a8a288fcf40383760843); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc857eac6ac27a8a288fcf40383760843)): ?>
<?php $component = $__componentOriginalc857eac6ac27a8a288fcf40383760843; ?>
<?php unset($__componentOriginalc857eac6ac27a8a288fcf40383760843); ?>
<?php endif; ?>
                            <div class="relative hidden sm:block" x-data="{ open: false }">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="open = !open"
                                >
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5" /></svg>
                                    <span><?php echo e(__('Last 30 days')); ?></span>
                                </button>
                                <div
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    x-cloak
                                    class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200/80 bg-white py-1 text-sm shadow-card dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800"><?php echo e(__('Last 7 days')); ?></button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800"><?php echo e(__('Last 30 days')); ?></button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800"><?php echo e(__('Quarter to date')); ?></button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="relative rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                title="<?php echo e(__('Notifications')); ?>"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.082A2.25 2.25 0 0021.75 14v-4.5a6 6 0 00-12 0v4.5a2.25 2.25 0 002.438 2.082M9 17.25h6" /></svg>
                                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-950"></span>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-transparent p-2 text-slate-500 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-white"
                                @click="cycleTheme()"
                                :title="theme === 'light' ? '<?php echo e(__('Switch to dark')); ?>' : (theme === 'dark' ? '<?php echo e(__('Match system')); ?>' : '<?php echo e(__('Switch to light')); ?>')"
                            >
                                <svg x-show="theme !== 'dark'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                                <svg x-show="theme === 'dark'" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                            </button>

                            <?php if(auth()->guard()->check()): ?>
                                <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48','contentClasses' => 'py-1.5 bg-white dark:bg-slate-900 rounded-xl shadow-card ring-1 ring-slate-200/80 dark:ring-slate-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48','content-classes' => 'py-1.5 bg-white dark:bg-slate-900 rounded-xl shadow-card ring-1 ring-slate-200/80 dark:ring-slate-700']); ?>
                                     <?php $__env->slot('trigger', null, []); ?> 
                                        <button type="button" class="flex items-center gap-2 rounded-2xl border border-slate-200/80 bg-white py-1.5 pl-1.5 pr-2 text-left shadow-sm transition hover:border-slate-300 hover:shadow-card dark:border-slate-700 dark:bg-slate-900">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold text-white">
                                                <?php echo e(mb_strtoupper(mb_substr(Auth::user()->name, 0, 2))); ?>

                                            </span>
                                            <span class="hidden min-w-0 sm:block">
                                                <span class="block max-w-[9rem] truncate text-xs font-semibold text-slate-900 dark:text-white"><?php echo e(Auth::user()->name); ?></span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400"><?php echo e($rbacActiveAssignment?->role?->name ?? __('No active role')); ?></span>
                                            </span>
                                            <svg class="hidden h-4 w-4 shrink-0 text-slate-400 sm:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                     <?php $__env->endSlot(); ?>
                                     <?php $__env->slot('content', null, []); ?> 
                                        <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('profile.edit'),'dataPradyFullNav' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit')),'data-prady-full-nav' => true]); ?><?php echo e(__('Profile')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'dataPradyFullNav' => true,'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'data-prady-full-nav' => true,'onclick' => 'event.preventDefault(); this.closest(\'form\').submit();']); ?>
                                                <?php echo e(__('Log Out')); ?>

                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                        </form>
                                     <?php $__env->endSlot(); ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <div class="relative bg-mesh-light dark:bg-mesh-dark">
                    <div class="pointer-events-none absolute inset-0 bg-slate-100/90 dark:bg-slate-950/80"></div>
                    <main class="relative mx-auto min-w-0 max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                        <div
                            x-show="workspaceLoading"
                            x-transition.opacity
                            x-cloak
                            class="absolute inset-0 z-20 rounded-2xl bg-white/75 px-4 py-6 backdrop-blur-[2px] dark:bg-slate-950/75 sm:px-6 lg:px-8"
                        >
                            <?php if (isset($component)) { $__componentOriginal947e8e11717ea9d23278673fa0ffd019 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal947e8e11717ea9d23278673fa0ffd019 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.prady-workspace-skeleton','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('prady-workspace-skeleton'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal947e8e11717ea9d23278673fa0ffd019)): ?>
<?php $attributes = $__attributesOriginal947e8e11717ea9d23278673fa0ffd019; ?>
<?php unset($__attributesOriginal947e8e11717ea9d23278673fa0ffd019); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal947e8e11717ea9d23278673fa0ffd019)): ?>
<?php $component = $__componentOriginal947e8e11717ea9d23278673fa0ffd019; ?>
<?php unset($__componentOriginal947e8e11717ea9d23278673fa0ffd019); ?>
<?php endif; ?>
                        </div>

                        <div
                            class="relative min-w-0 transition-opacity duration-200"
                            :class="workspaceLoading ? 'pointer-events-none opacity-40' : 'opacity-100'"
                        >
                            <?php echo e($slot); ?>

                        </div>
                    </main>

                    <footer class="relative border-t border-slate-200/60 bg-white/60 px-4 py-4 text-xs text-slate-500 backdrop-blur dark:border-slate-800/60 dark:bg-slate-950/40 dark:text-slate-400 sm:px-6 lg:px-8">
                        <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-2">
                            <span>© <?php echo e(now()->year); ?> PradytecAI. <?php echo e(__('All rights reserved.')); ?></span>
                            <span class="tabular-nums text-slate-400"><?php echo e(__('Version')); ?> <?php echo e(config('app.version', '1.0.0')); ?></span>
                        </div>
                    </footer>
                </div>
            </div>

            <?php if(auth()->guard()->check()): ?>
                <?php if (isset($component)) { $__componentOriginal635b39ef5be33bd3b6e46d3bb11dba21 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal635b39ef5be33bd3b6e46d3bb11dba21 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.command-palette','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('command-palette'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal635b39ef5be33bd3b6e46d3bb11dba21)): ?>
<?php $attributes = $__attributesOriginal635b39ef5be33bd3b6e46d3bb11dba21; ?>
<?php unset($__attributesOriginal635b39ef5be33bd3b6e46d3bb11dba21); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal635b39ef5be33bd3b6e46d3bb11dba21)): ?>
<?php $component = $__componentOriginal635b39ef5be33bd3b6e46d3bb11dba21; ?>
<?php unset($__componentOriginal635b39ef5be33bd3b6e46d3bb11dba21); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\HP\Desktop\Newfolder\pradydashboard\resources\views/components/prady-shell.blade.php ENDPATH**/ ?>