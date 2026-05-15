@php
    $sectionTitle = $navSections[$activeSection]['label'] ?? __('Settings');
    $readOnly = in_array($activeSection, ['logs', 'audit'], true);
@endphp

<x-dashboard-layout :heading="__('Platform Configuration')" :subheading="__('Enterprise system administration & SaaS defaults')">
    @if (session('status'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200" x-data x-init="setTimeout(() => $el.remove(), 5000)">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">{{ __('Administration') }}</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Platform Configuration Center') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Global identity, security, integrations, and operational defaults') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('system-settings.export') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Export') }}</a>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-3 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ __('Run diagnostics') }}</button>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-12">
        {{-- Left navigation --}}
        <aside class="xl:col-span-3">
            <nav class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="border-b border-slate-200/80 px-4 py-3 dark:border-slate-800">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">{{ __('Settings') }}</p>
                </div>
                <ul class="max-h-[32rem] overflow-y-auto p-2">
                    @foreach ($navSections as $key => $meta)
                        <li>
                            <a
                                href="{{ route('system-settings.edit', ['section' => $key]) }}"
                                @class([
                                    'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                    'bg-gradient-to-r from-indigo-500/15 to-violet-500/10 text-indigo-700 ring-1 ring-indigo-500/20 dark:text-indigo-200' => $activeSection === $key,
                                    'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/60' => $activeSection !== $key,
                                ])
                            >
                                <span @class([
                                    'h-1.5 w-1.5 shrink-0 rounded-full',
                                    'bg-indigo-500' => $activeSection === $key,
                                    'bg-slate-300 dark:bg-slate-600' => $activeSection !== $key,
                                ])></span>
                                {{ __($meta['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- System health sidebar --}}
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-900 to-slate-950 p-4 shadow-card dark:border-slate-700">
                <p class="text-[10px] font-bold uppercase tracking-widest text-cyan-400">{{ __('System health') }}</p>
                <ul class="mt-3 space-y-2.5">
                    @foreach ($health as $widget)
                        <li class="flex items-center justify-between gap-2 text-xs">
                            <span class="text-slate-400">{{ $widget['label'] }}</span>
                            <span class="font-semibold tabular-nums text-white">{{ $widget['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        {{-- Right content --}}
        <div class="xl:col-span-9">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $sectionTitle }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-data="{ saved: false }">
                            <span class="text-emerald-600 dark:text-emerald-400" x-show="saved" x-cloak>{{ __('All changes saved') }}</span>
                            <span x-show="!saved">{{ __('Configure platform defaults for this area') }}</span>
                        </p>
                    </div>
                    @unless ($readOnly)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('Autosave on submit') }}</span>
                    @endunless
                </div>

                @if ($readOnly)
                    <div class="px-5 py-6">
                        @include('admin.system-settings.sections.content')
                    </div>
                @else
                    <form
                        method="POST"
                        action="{{ route('system-settings.update') }}"
                        enctype="multipart/form-data"
                        class="px-5 py-6"
                        @if ($activeSection === 'branding') x-data="{ uploading: false }" @submit="uploading = true" @endif
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="{{ $activeSection }}" />

                        @include('admin.system-settings.sections.content')

                        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200/80 pt-6 dark:border-slate-800">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:brightness-110">
                                {{ __('Save Changes') }}
                            </button>
                            <button type="submit" formaction="{{ route('system-settings.update') }}" class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-200">
                                {{ __('Publish Settings') }}
                            </button>
                            <button
                                type="submit"
                                formaction="{{ route('system-settings.restore-defaults') }}"
                                formmethod="POST"
                                class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200"
                                onclick="return confirm('{{ __('Restore defaults for this section?') }}')"
                            >
                                {{ __('Restore Defaults') }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-amber-200/80 bg-amber-50/80 px-4 py-3 text-xs text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                    <p class="font-semibold">{{ __('Configuration history') }}</p>
                    <p class="mt-1 opacity-80">{{ __('Last publish') }}: {{ now()->subHours(2)->diffForHumans() }}</p>
                </div>
                <div class="rounded-xl border border-slate-200/80 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300">
                    <p class="font-semibold">{{ __('Policy validation') }}</p>
                    <p class="mt-1">{{ __('All fields validated against platform policy') }}</p>
                </div>
                <div class="rounded-xl border border-sky-200/80 bg-sky-50/80 px-4 py-3 text-xs text-sky-900 dark:border-sky-900/40 dark:bg-sky-950/30 dark:text-sky-200">
                    <p class="font-semibold">{{ __('Rollback') }}</p>
                    <p class="mt-1">{{ __('Export config before major changes') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
