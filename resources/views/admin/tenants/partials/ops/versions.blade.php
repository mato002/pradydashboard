@php
    $subscriptionUrl = fn (int $id) => route('tenants.show', $tenant).'?tab=versions&subscription='.$id;
    $tracking = $selectedSubscription?->versionTracking;
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900';
    $inferredStatus = $inferredVersionStatus ?? 'unknown';
@endphp

@if ($tenant->projectSubscriptions->isEmpty())
    <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
        {{ __('No project subscriptions for this tenant. Add a subscription on the Projects tab first.') }}
    </p>
@else
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Manual version tracking per product subscription.') }}</p>
            @if ($selectedSubscription)
                <p class="mt-1 text-xs text-gray-500">{{ __('Project') }}: <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $selectedSubscription->project?->name }}</span></p>
            @endif
        </div>
        <div class="min-w-[14rem]">
            <label for="version-subscription-picker" class="block text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Project subscription') }}</label>
            <select id="version-subscription-picker" class="{{ $inputClass }}" onchange="window.location.href = this.value">
                @foreach ($tenant->projectSubscriptions as $sub)
                    <option value="{{ $subscriptionUrl($sub->id) }}" @selected($selectedSubscription?->id === $sub->id)>
                        {{ $sub->project?->name }} — {{ $sub->package_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($projectVersionContext['current'] || $projectVersionContext['latest'])
        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950">
            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ __('Project version registry') }}</p>
            <p class="mt-1 text-slate-600 dark:text-slate-400">
                {{ __('Current') }}: <span class="font-mono">{{ $projectVersionContext['current'] ?? __('Not set') }}</span>
                · {{ __('Latest') }}: <span class="font-mono">{{ $projectVersionContext['latest'] ?? __('Not set') }}</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Automated polling from /api/system/info is not enabled yet.') }}</p>
        </div>
    @endif

    @if ($tracking?->current_version && $projectVersionContext['current'] && version_compare($tracking->current_version, $projectVersionContext['current'], '<'))
        <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
            {{ __('Outdated') }} — {{ __('Tenant is on :tenant but project current is :project.', ['tenant' => $tracking->current_version, 'project' => $projectVersionContext['current']]) }}
        </p>
    @elseif (! $tracking?->current_version)
        <p class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
            {{ __('Unknown') }} — {{ __('No tenant version recorded yet.') }}
        </p>
    @endif

    <form method="post" action="{{ route('tenants.project-subscriptions.version.update', [$tenant, $selectedSubscription]) }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">{{ __('Current version') }}</label>
                <input type="text" name="current_version" value="{{ old('current_version', $tracking?->current_version) }}" class="{{ $inputClass.' font-mono' }}" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Version deployed for this tenant.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Latest version') }}</label>
                <input type="text" name="latest_version" value="{{ old('latest_version', $tracking?->latest_version ?? $projectVersionContext['latest']) }}" class="{{ $inputClass.' font-mono' }}" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Target or available version.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Update status') }}</label>
                <select name="update_status" class="{{ $inputClass }}" required>
                    @foreach ($updateStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('update_status', $tracking?->update_status ?? $inferredStatus) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">{{ __('Inferred: :status', ['status' => ucfirst(str_replace('_', ' ', $inferredStatus))]) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Commit hash') }}</label>
                <input type="text" name="commit_hash" value="{{ old('commit_hash', $tracking?->commit_hash) }}" class="{{ $inputClass.' font-mono' }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Build number') }}</label>
                <input type="text" name="build_number" value="{{ old('build_number', $tracking?->build_number) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Last checked date') }}</label>
                <input type="date" name="last_checked_at" value="{{ old('last_checked_at', optional($tracking?->last_checked_at)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Last updated date') }}</label>
                <input type="date" name="last_updated_at" value="{{ old('last_updated_at', optional($tracking?->last_updated_at)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium">{{ __('Update notes') }}</label>
                <textarea name="update_notes" rows="3" class="{{ $inputClass }}">{{ old('update_notes', $tracking?->update_notes) }}</textarea>
            </div>
        </div>

        <x-primary-button>{{ __('Save version tracking') }}</x-primary-button>
    </form>
@endif
