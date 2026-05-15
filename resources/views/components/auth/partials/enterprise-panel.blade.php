@php
    $features = [
        ['icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15.75 10.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z', 'label' => __('Tenant lifecycle management')],
        ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'label' => __('Real-time infrastructure monitoring')],
        ['icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182', 'label' => __('Automated deployments & rollback')],
        ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'label' => __('SSL & domain lifecycle management')],
        ['icon' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z', 'label' => __('API credentials & webhook governance')],
        ['icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25m2.25 0v.375c0 .621-.504 1.125-1.125 1.125H21m0 0v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V6.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V4.5m15.75 0v.75a.75.75 0 0 1-.75.75H6a.75.75 0 0 1-.75-.75V4.5', 'label' => __('Billing & subscription automation')],
        ['icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375', 'label' => __('Backup orchestration')],
        ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z', 'label' => __('Access control & RBAC')],
        ['icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z', 'label' => __('Activity logging & audit trails')],
        ['icon' => 'M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.737a4.5 4.5 0 0 1 2.7-.9H18.75a4.5 4.5 0 0 1 2.7.9l1.087 1.087a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3', 'label' => __('Multi-server orchestration')],
    ];

    $metrics = [
        ['label' => __('Active Tenants'), 'base' => 58, 'variance' => 2, 'suffix' => '', 'decimals' => 0],
        ['label' => __('Infrastructure Uptime'), 'base' => 99.98, 'variance' => 0.02, 'suffix' => '%', 'decimals' => 2],
        ['label' => __('Deployments Today'), 'base' => 24, 'variance' => 3, 'suffix' => '', 'decimals' => 0],
        ['label' => __('Active Servers'), 'base' => 12, 'variance' => 0, 'suffix' => '', 'decimals' => 0],
        ['label' => __('SSL Certificates'), 'base' => 47, 'variance' => 1, 'suffix' => '', 'decimals' => 0],
        ['label' => __('API Requests'), 'base' => 18420, 'variance' => 800, 'suffix' => '', 'decimals' => 0],
        ['label' => __('Security Score'), 'base' => 98, 'variance' => 1, 'suffix' => '', 'decimals' => 0],
        ['label' => __('Avg Response'), 'base' => 42, 'variance' => 4, 'suffix' => 'ms', 'decimals' => 0],
    ];
@endphp

<aside class="relative hidden min-h-[280px] flex-col overflow-hidden bg-enterprise-mesh lg:flex lg:min-h-0 lg:w-[52%] lg:shrink-0 xl:w-[50%]">
    <div class="pointer-events-none absolute inset-0 bg-auth-glow animate-auth-mesh-shift opacity-80" aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 opacity-[0.35]"
        style="background-image: linear-gradient(to right, rgba(148, 163, 184, 0.07) 1px, transparent 1px), linear-gradient(to bottom, rgba(148, 163, 184, 0.07) 1px, transparent 1px); background-size: 40px 40px"
        aria-hidden="true"
    ></div>
    <div class="pointer-events-none absolute -left-32 top-0 h-[28rem] w-[28rem] rounded-full bg-violet-600/30 blur-3xl animate-auth-blob" aria-hidden="true"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 h-[24rem] w-[24rem] translate-x-1/4 translate-y-1/4 rounded-full bg-cyan-500/20 blur-3xl animate-auth-blob-slow" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-1/3 top-16 h-40 w-40 rounded-full bg-indigo-500/25 blur-2xl animate-auth-glow-pulse" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        @foreach ([12, 28, 45, 62, 78, 88, 22, 55, 70, 35, 8, 92, 18, 48, 65, 82, 38, 72, 5, 58, 25, 42, 75, 15] as $i => $left)
            <span
                class="absolute h-1 w-1 rounded-full bg-cyan-300/60 animate-auth-particle"
                style="left: {{ $left }}%; top: {{ 15 + ($i * 3.2) % 70 }}%; animation-delay: {{ $i * 0.35 }}s; animation-duration: {{ 5 + ($i % 4) }}s"
            ></span>
        @endforeach
    </div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-cyan-400/40 to-transparent animate-auth-border-glow" aria-hidden="true"></div>

    <div class="relative z-[1] flex flex-1 flex-col justify-between gap-6 px-6 py-8 sm:px-8 sm:py-9 lg:px-10 lg:py-10 xl:px-12">
        <div class="animate-auth-fade-up">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 transition-opacity hover:opacity-90">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 shadow-lg ring-1 ring-white/20 backdrop-blur-md">
                    <x-brand-logo class="h-7 w-7 text-cyan-300" />
                </span>
                <span>
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.28em] text-cyan-200/80">PradytecAI</span>
                    <span class="block text-base font-bold tracking-tight text-white">{{ __('Operations Cloud') }}</span>
                </span>
            </a>
            <span class="mt-4 inline-flex items-center gap-2 rounded-full border border-cyan-400/25 bg-cyan-500/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-cyan-200 ring-1 ring-cyan-400/20">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-70"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                </span>
                {{ __('Enterprise Infrastructure Platform') }}
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-5 lg:gap-6 animate-auth-fade-up-delay">
            <div>
                <h1 class="max-w-xl text-2xl font-extrabold leading-[1.12] tracking-tight text-white sm:text-[1.75rem] xl:text-[2rem]">
                    {{ __('Operate your SaaS infrastructure from one intelligent control plane.') }}
                </h1>
                <p class="mt-3 max-w-xl text-[13px] leading-relaxed text-slate-300/90 sm:text-sm">
                    {{ __('Orchestrate tenants, monitor infrastructure health, ship deployments, manage SSL lifecycles, automate billing, enforce access control, and maintain full observability—from a single enterprise-grade operations layer built for scale.') }}
                </p>
            </div>

            <ul class="grid max-w-xl gap-0.5 sm:grid-cols-2 sm:gap-x-4">
                @foreach ($features as $feature)
                    <li class="auth-feature-row group">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500/25 to-cyan-500/15 text-cyan-300 shadow-[0_0_12px_-2px_rgba(34,211,238,0.4)] ring-1 ring-cyan-400/20 transition group-hover:shadow-[0_0_16px_-2px_rgba(34,211,238,0.55)]">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                            </svg>
                        </span>
                        <span class="leading-snug">{{ $feature['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="animate-auth-fade-up-delay-2">
            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">{{ __('Live platform telemetry') }}</p>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-emerald-300">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    </span>
                    {{ __('Operational') }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-2.5">
                @foreach ($metrics as $i => $metric)
                    <div class="auth-metric-tile group" style="animation-delay: {{ $i * 0.4 }}s">
                        <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400 leading-tight">{{ $metric['label'] }}</p>
                        <p
                            class="mt-1 font-mono text-base font-bold tabular-nums text-white sm:text-lg"
                            x-data="liveMetric({{ $metric['base'] }}, {{ $metric['variance'] }}, '{{ $metric['suffix'] }}', {{ $metric['decimals'] }})"
                            x-text="display"
                        >—</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-2.5 text-[10px] text-slate-500">{{ __('Illustrative metrics for demo environments') }}</p>
        </div>
    </div>
</aside>
