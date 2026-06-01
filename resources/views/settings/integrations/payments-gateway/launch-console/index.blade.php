@php
    $navItems = [
        ['href' => '#gateway-status', 'label' => __('Gateway status')],
        ['href' => '#paybill-readiness', 'label' => __('PayBill readiness')],
        ['href' => '#validation-workspace', 'label' => __('Validation workspace')],
        ['href' => '#validation-history', 'label' => __('Validation history')],
        ['href' => '#incident-escalation', 'label' => __('Incident escalation')],
    ];
@endphp

<x-dashboard-layout :heading="__('Payments Gateway')" :subheading="__('Launch Console')">
    <div class="space-y-6">
        @include('settings.integrations.payments-gateway.partials.header')
        @include('settings.integrations.payments-gateway.partials.alerts')

        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-100">
            <p class="font-semibold">{{ __('Operational launch console — not a sandbox simulator') }}</p>
            <p class="mt-1 text-xs">{{ __('This workspace orchestrates controlled treasury deployment on payments.pradytecai.com. Transaction truth stays on the gateway; the dashboard never initiates Daraja payments.') }}</p>
        </div>

        @if ($gatewayUnavailable ?? false)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                {{ $gatewayMessage }}
            </div>
        @else
            @if ($isLiveEnvironment ?? false)
                <div class="rounded-xl border border-rose-300 bg-rose-100 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-rose-900 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-100">
                    {{ __('Live financial environment') }}
                </div>
            @endif

            <nav class="flex flex-wrap gap-2 rounded-xl border border-slate-200/80 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-900/60">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-white hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-300">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            @if (! empty($missingApis))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                    <p class="font-semibold">{{ __('Some gateway APIs are not available yet') }}</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($missingApis as $endpoint)
                            <li>{{ $endpoint }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section id="gateway-status" class="scroll-mt-24">
                @include('settings.integrations.payments-gateway.launch-console.partials.operational-status')
            </section>

            <section id="paybill-readiness" class="scroll-mt-24">
                @include('settings.integrations.payments-gateway.launch-console.partials.paybill-readiness')
            </section>

            <section id="validation-workspace" class="scroll-mt-24">
                @include('settings.integrations.payments-gateway.launch-console.partials.validation-workspace')
            </section>

            <section id="validation-history" class="scroll-mt-24">
                <div
                    data-launch-panel="validation-history"
                    data-panel-url="{{ route('settings.payments-gateway.launch-console.panel', ['panel' => 'validation-history', 'paybill_account_uuid' => $filters['paybill_account_uuid'] ?? '', 'environment' => $filters['environment'] ?? 'production']) }}"
                >
                    @include('settings.integrations.payments-gateway.launch-console.partials.validation-history')
                </div>
            </section>

            <section id="incident-escalation" class="scroll-mt-24">
                <div
                    data-launch-panel="incidents"
                    data-panel-url="{{ route('settings.payments-gateway.launch-console.panel', ['panel' => 'incidents']) }}"
                >
                    @include('settings.integrations.payments-gateway.launch-console.partials.incident-escalation')
                </div>
            </section>
        @endif
    </div>

    @unless ($gatewayUnavailable ?? false)
        <script>
            document.querySelectorAll('[data-launch-panel]').forEach((container) => {
                const url = container.dataset.panelUrl;
                if (!url) return;

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.text())
                    .then((html) => { container.innerHTML = html; })
                    .catch(() => {});
            });
        </script>
    @endunless
</x-dashboard-layout>
