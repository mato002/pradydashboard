<div id="incident-panels" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
    <div>
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Incident panels') }}</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Expand an incident class to review impact and run safe remediation actions.') }}</p>
    </div>

    <div class="space-y-3">
        @foreach ($incidents as $panelKey => $panel)
            <details @class([
                'group rounded-xl border',
                'border-rose-200/80 dark:border-rose-900' => $panel['count'] > 0 && in_array($panelKey, ['dead_letters', 'critical_alerts'], true),
                'border-amber-200/80 dark:border-amber-900' => $panel['count'] > 0 && ! in_array($panelKey, ['dead_letters', 'critical_alerts'], true),
                'border-slate-200/80 dark:border-slate-800' => $panel['count'] === 0,
            ])>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $panel['title'] }}</span>
                        <x-ui.status-badge :variant="$panel['count'] > 0 ? 'danger' : 'success'">{{ $panel['count'] }}</x-ui.status-badge>
                    </div>
                    <span class="text-xs text-slate-500 group-open:hidden">{{ __('Expand') }}</span>
                </summary>

                <div class="border-t border-slate-200/80 px-4 py-4 dark:border-slate-800">
                    @if ($panelKey === 'dead_letters' && ! ($quickActions['replay_dead_letter']['available'] ?? false))
                        <p class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ __('Operation API not available yet.') }}</p>
                    @endif
                    @if ($panel['count'] === 0)
                        <p class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700">{{ $panel['empty_message'] }}</p>
                    @else
                        @include('settings.integrations.payments-gateway.operations-console.partials.incident-bulk-toolbar', [
                            'panelKey' => $panelKey,
                            'panel' => $panel,
                        ])

                        <div class="space-y-3">
                            @foreach ($panel['items'] as $item)
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        @if ($item['bulk_selectable'] ?? false)
                                            @permission('payments_gateway.manage')
                                                <label class="mt-1 flex items-start gap-2">
                                                    <input
                                                        type="checkbox"
                                                        form="bulk-form-{{ $panelKey }}"
                                                        name="uuids[]"
                                                        value="{{ $item['bulk_uuid'] }}"
                                                        data-bulk-panel="{{ $panelKey }}"
                                                        class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600"
                                                    >
                                                    <span class="sr-only">{{ __('Select for bulk action') }}</span>
                                                </label>
                                            @endpermission
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <x-ui.status-badge :variant="$incidentSeverityTone($item['severity'])">{{ ucfirst($item['severity']) }}</x-ui.status-badge>
                                                <span class="text-xs text-slate-500">{{ $item['age'] }}</span>
                                            </div>
                                            <p class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                                            @if (filled($item['description']))
                                                <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($item['description'], 120) }}</p>
                                            @endif
                                            <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                                                @if ($panelKey === 'dead_letters')
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Type') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ $item['type'] }}</dd></div>
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Queue') }}</dt><dd class="font-mono text-slate-800 dark:text-slate-200">{{ $item['queue'] }}</dd></div>
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Status') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ ucfirst($item['status']) }}</dd></div>
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Age') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ $item['age'] }}</dd></div>
                                                @else
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Tenant') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ $item['tenant_name'] }}</dd></div>
                                                    <div><dt class="font-semibold text-slate-500">{{ __('Profile') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ $item['payment_profile_label'] }}</dd></div>
                                                    <div><dt class="font-semibold text-slate-500">{{ __('PayBill') }}</dt><dd class="text-slate-800 dark:text-slate-200">{{ $item['paybill_label'] }}</dd></div>
                                                    @if (($item['webhook_endpoint'] ?? '—') !== '—')
                                                        <div class="sm:col-span-2"><dt class="font-semibold text-slate-500">{{ __('Webhook endpoint') }}</dt><dd class="truncate font-mono text-slate-800 dark:text-slate-200">{{ $item['webhook_endpoint'] }}</dd></div>
                                                    @endif
                                                @endif
                                            </dl>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            @if (filled($item['investigate_url'] ?? null))
                                                <a href="{{ $item['investigate_url'] }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Investigate') }}</a>
                                            @endif
                                            @if (filled($item['tenant_mapping_url']))
                                                <a href="{{ $item['tenant_mapping_url'] }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Treasury mapping') }}</a>
                                            @endif
                                            @include('settings.integrations.payments-gateway.operations-console.partials.incident-actions', [
                                                'panelKey' => $panelKey,
                                                'item' => $item,
                                                'action' => $panel['action'],
                                            ])
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
        @endforeach
    </div>
</div>

@once
    <script>
        window.updateOperationsBulkSelectedCount = function (panelKey) {
            const counter = document.getElementById('bulk-selected-' + panelKey);
            if (! counter) {
                return;
            }

            counter.textContent = String(document.querySelectorAll('[data-bulk-panel="' + panelKey + '"]:checked').length);
        };

        window.toggleOperationsBulkComments = function (panelKey) {
            const select = document.getElementById('bulk-action-' + panelKey);
            const wrap = document.getElementById('bulk-comments-wrap-' + panelKey);
            if (! select || ! wrap) {
                return;
            }

            const action = select.value;
            const acceptsComments = action === 'alerts.acknowledge' || action === 'alerts.resolve';
            wrap.classList.toggle('hidden', ! acceptsComments);
        };

        window.confirmOperationsBulkAction = function (panelKey, actions) {
            const form = document.getElementById('bulk-form-' + panelKey);
            if (! form) {
                return false;
            }

            const selected = document.querySelectorAll('[data-bulk-panel="' + panelKey + '"]:checked').length;
            if (selected === 0) {
                alert(@js(__('Select at least one incident row before running a bulk action.')));
                return false;
            }

            const action = form.querySelector('[name="action"]')?.value ?? '';
            if (! action) {
                alert(@js(__('Choose a bulk action before submitting.')));
                return false;
            }

            const config = actions[action] ?? null;
            if (config?.requires_confirm && config?.confirm) {
                return confirm(config.confirm);
            }

            return true;
        };

        document.addEventListener('change', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLInputElement) || ! target.matches('[data-bulk-panel]')) {
                return;
            }

            window.updateOperationsBulkSelectedCount(target.dataset.bulkPanel);
        });
    </script>
@endonce
