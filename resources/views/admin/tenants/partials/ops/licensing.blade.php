@forelse ($tenant->projectSubscriptions as $sub)
    <div class="mb-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
        <p class="font-semibold text-gray-900 dark:text-white">{{ $sub->project?->name }}</p>
        <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-gray-500">{{ __('License status') }}</dt><dd class="capitalize">{{ $sub->license_status }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Product status') }}</dt><dd class="capitalize">{{ $sub->product_status }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Kill switch') }}</dt><dd>{{ $sub->kill_switch_enabled ? __('Enabled') : __('Off') }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Grace period') }}</dt><dd>{{ $sub->grace_period_days ?? '—' }} {{ __('days') }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Last license check') }}</dt><dd>{{ $sub->last_license_check_at?->diffForHumans() ?? __('Never') }}</dd></div>
            <div><dt class="text-gray-500">{{ __('Disabled reason') }}</dt><dd>{{ $sub->disabled_reason ?? '—' }}</dd></div>
        </dl>
        <form method="post" action="{{ route('tenants.project-subscriptions.license.grace', [$tenant, $sub]) }}" class="mt-3 flex items-center gap-2">
            @csrf
            <input type="number" name="days" min="1" max="365" value="{{ $sub->grace_period_days ?? 7 }}" class="w-20 rounded border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Update grace') }}</button>
        </form>
    </div>
@empty
    <p class="text-sm text-gray-500">{{ __('No project subscriptions — license controls appear per subscribed product.') }}</p>
@endforelse
