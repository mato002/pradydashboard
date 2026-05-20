@php
    $subscriptionUrl = fn (int $id) => route('tenants.show', $tenant).'?tab=modules&subscription='.$id;
@endphp

@if ($tenant->projectSubscriptions->isEmpty())
    <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
        {{ __('No project subscriptions for this tenant. Add a subscription on the Projects tab first.') }}
    </p>
@else
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Manage modules for a hosted product subscription.') }}</p>
            @if ($selectedSubscription)
                <p class="mt-1 text-xs text-gray-500">{{ __('Project') }}: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedSubscription->project?->name }}</span></p>
            @endif
        </div>
        <div class="min-w-[14rem]">
            <label for="subscription-picker" class="block text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Project subscription') }}</label>
            <select
                id="subscription-picker"
                class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"
                onchange="window.location.href = this.value"
            >
                @foreach ($tenant->projectSubscriptions as $sub)
                    <option value="{{ $subscriptionUrl($sub->id) }}" @selected($selectedSubscription?->id === $sub->id)>
                        {{ $sub->project?->name }} — {{ $sub->package_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($moduleRows->isEmpty())
        <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
            {{ __('No modules defined for this project.') }}
        </p>
    @else
        <form
            method="post"
            action="{{ route('tenants.project-subscriptions.modules.update', [$tenant, $selectedSubscription]) }}"
            class="space-y-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">{{ __('Module') }}</th>
                            <th class="px-4 py-3">{{ __('Enabled') }}</th>
                            <th class="px-4 py-3">{{ __('Subscribed') }}</th>
                            <th class="px-4 py-3">{{ __('Billing status') }}</th>
                            <th class="px-4 py-3">{{ __('Price override') }}</th>
                            <th class="px-4 py-3">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($moduleRows as $entry)
                            @php
                                $module = $entry['module'];
                                $row = $entry['row'];
                                $mid = $module->id;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $module->name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $module->code }}</p>
                                    @if ($module->monthly_price)
                                        <p class="mt-1 text-xs text-gray-500">{{ __('Default') }}: {{ number_format((float) $module->monthly_price, 2) }}/{{ __('mo') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="modules[{{ $mid }}][enabled]" value="0" />
                                    <input type="checkbox" name="modules[{{ $mid }}][enabled]" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(old("modules.{$mid}.enabled", $entry['enabled'])) />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="modules[{{ $mid }}][subscribed]" value="0" />
                                    <input type="checkbox" name="modules[{{ $mid }}][subscribed]" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(old("modules.{$mid}.subscribed", $entry['subscribed'])) />
                                </td>
                                <td class="px-4 py-3">
                                    <select name="modules[{{ $mid }}][billing_status]" class="w-full min-w-[7rem] rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-900">
                                        @foreach ($moduleBillingOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old("modules.{$mid}.billing_status", $row?->billing_status ?? 'active') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="modules[{{ $mid }}][monthly_price_override]"
                                        value="{{ old("modules.{$mid}.monthly_price_override", $row?->monthly_price_override) }}"
                                        class="w-28 rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-900"
                                        placeholder="—"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="text"
                                        name="modules[{{ $mid }}][notes]"
                                        value="{{ old("modules.{$mid}.notes", $row?->notes) }}"
                                        class="w-full min-w-[10rem] rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-900"
                                        placeholder="{{ __('Optional') }}"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-4 py-4 dark:border-gray-800">
                <x-primary-button>{{ __('Save module subscriptions') }}</x-primary-button>
            </div>
        </form>
    @endif
@endif
