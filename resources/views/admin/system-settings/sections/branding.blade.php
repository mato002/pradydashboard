@php $b = $sectionData; @endphp

<div class="grid gap-6 lg:grid-cols-2" x-data="{ primary: '{{ old('primary_color', $b['primary_color'] ?? '#4f46e5') }}', accent: '{{ old('accent_color', $b['accent_color'] ?? '#06b6d4') }}' }">
    <div class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['key' => 'logo', 'label' => __('Light logo'), 'url' => $logoUrl],
                ['key' => 'logo_dark', 'label' => __('Dark logo'), 'url' => $logoDarkUrl],
                ['key' => 'favicon', 'label' => __('Favicon'), 'url' => $faviconUrl],
            ] as $asset)
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $asset['label'] }}</p>
                    <div class="mt-2 flex h-16 items-center justify-center overflow-hidden rounded-lg bg-white dark:bg-slate-900">
                        @if ($asset['url'])
                            <img src="{{ $asset['url'] }}" alt="" class="max-h-full max-w-full object-contain p-1" />
                        @else
                            <x-brand-logo class="h-8 w-8 text-sm" />
                        @endif
                    </div>
                    <input type="file" name="{{ $asset['key'] }}" accept="image/*" class="mt-2 block w-full text-[10px] text-slate-500 file:mr-1 file:rounded file:border-0 file:bg-indigo-50 file:px-2 file:py-1 file:text-[10px] file:font-semibold file:text-indigo-700" />
                    <p class="mt-1 flex items-center gap-1 text-[9px] text-emerald-600"><span class="h-1 w-1 rounded-full bg-emerald-500"></span> CDN sync</p>
                </div>
            @endforeach
        </div>

        <div>
            <x-input-label for="logo_url" :value="__('Logo URL (CDN)')" />
            <x-text-input id="logo_url" name="logo_url" type="url" class="mt-1 w-full" :value="old('logo_url', filter_var($logoPath ?? '', FILTER_VALIDATE_URL) ? $logoPath : '')" />
        </div>

        @if ($logoPath)
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600" />
                {{ __('Remove light logo') }}
            </label>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="primary_color" :value="__('Primary color')" />
                <input type="color" id="primary_color" name="primary_color" x-model="primary" class="mt-1 h-10 w-full cursor-pointer rounded-lg border border-slate-200 dark:border-slate-700" />
            </div>
            <div>
                <x-input-label for="accent_color" :value="__('Accent color')" />
                <input type="color" id="accent_color" name="accent_color" x-model="accent" class="mt-1 h-10 w-full cursor-pointer rounded-lg border border-slate-200 dark:border-slate-700" />
            </div>
            <div>
                <x-input-label for="font_family" :value="__('Typography')" />
                <select id="font_family" name="font_family" class="mt-1 w-full rounded-xl border-slate-200 text-sm dark:border-slate-700 dark:bg-slate-800">
                    @foreach (['Inter', 'DM Sans', 'IBM Plex Sans', 'Geist'] as $font)
                        <option value="{{ $font }}" @selected(($b['font_family'] ?? 'Inter') === $font)>{{ $font }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="sidebar_style" :value="__('Sidebar style')" />
                <select id="sidebar_style" name="sidebar_style" class="mt-1 w-full rounded-xl border-slate-200 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="gradient" @selected(($b['sidebar_style'] ?? '') === 'gradient')>{{ __('Gradient') }}</option>
                    <option value="solid" @selected(($b['sidebar_style'] ?? '') === 'solid')>{{ __('Solid') }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="login_tagline" :value="__('Login tagline')" />
                <x-text-input id="login_tagline" name="login_tagline" class="mt-1 w-full" :value="old('login_tagline', $b['login_tagline'] ?? '')" />
            </div>
        </div>
    </div>

    {{-- Live preview --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 shadow-card dark:border-slate-700">
        <div class="px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-white" :style="'background: linear-gradient(135deg, '+primary+', '+accent+')'">{{ __('Live preview') }}</div>
        <div class="bg-slate-950 p-4">
            <div class="rounded-xl border border-white/10 bg-slate-900 p-4">
                <div class="flex items-center gap-2">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" class="h-8 w-8 object-contain" alt="" />
                    @else
                        <x-brand-logo class="h-8 w-8 text-sm" />
                    @endif
                    <span class="text-sm font-semibold text-white">{{ $platformConfig['general']['platform_name'] ?? 'Platform' }}</span>
                </div>
                <p class="mt-3 text-xs text-slate-400">{{ $b['login_tagline'] ?? '' }}</p>
                <button type="button" class="mt-4 w-full rounded-lg py-2 text-xs font-semibold text-white" :style="'background:'+primary">{{ __('Sign in') }}</button>
            </div>
            <p class="mt-3 text-center text-[10px] text-slate-500">{{ __('Sidebar') }} · {{ $b['sidebar_style'] ?? 'gradient' }} · {{ $b['font_family'] ?? 'Inter' }}</p>
        </div>
    </div>
</div>
