@php
    $panelBulk = $bulkActions[$panelKey] ?? null;
    $selectableCount = collect($panel['items'] ?? [])->where('bulk_selectable', true)->count();
@endphp

@if ($panelBulk && ($panelBulk['enabled'] ?? false) && $selectableCount > 0)
    @permission('payments_gateway.manage')
        <form
            id="bulk-form-{{ $panelKey }}"
            method="post"
            action="{{ route('settings.payments-gateway.operations-console.bulk-action') }}"
            class="mb-4 rounded-xl border border-indigo-200/80 bg-indigo-50/60 p-4 dark:border-indigo-900 dark:bg-indigo-950/30"
            onsubmit="return window.confirmOperationsBulkAction('{{ $panelKey }}', @js($panelBulk['actions'] ?? []))"
        >
            @csrf
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">{{ __('Bulk remediation') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                        <span id="bulk-selected-{{ $panelKey }}">0</span> / {{ $selectableCount }} {{ __('selected') }}
                    </p>
                </div>
                <div class="min-w-[12rem] flex-1">
                    <label for="bulk-action-{{ $panelKey }}" class="sr-only">{{ __('Bulk action') }}</label>
                    <select
                        id="bulk-action-{{ $panelKey }}"
                        name="action"
                        required
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900"
                        onchange="window.toggleOperationsBulkComments('{{ $panelKey }}')"
                    >
                        <option value="">{{ __('Choose action…') }}</option>
                        @foreach ($panelBulk['actions'] ?? [] as $actionKey => $actionConfig)
                            <option value="{{ $actionKey }}">{{ $actionConfig['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="bulk-comments-wrap-{{ $panelKey }}" class="hidden min-w-[14rem] flex-1">
                    <label for="bulk-comments-{{ $panelKey }}" class="sr-only">{{ __('Comments') }}</label>
                    <input
                        id="bulk-comments-{{ $panelKey }}"
                        type="text"
                        name="comments"
                        maxlength="500"
                        placeholder="{{ __('Comments (optional)') }}"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900"
                    >
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                    {{ __('Run bulk action') }}
                </button>
            </div>
        </form>
    @endpermission
@endif
