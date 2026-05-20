<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tenant subscriptions to hosted products.') }}</p>
        <form method="post" action="{{ route('tenants.project-subscriptions.sync', $tenant) }}">
            @csrf
            <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">{{ __('Sync primary project') }}</button>
        </form>
    </div>

    @forelse ($tenant->projectSubscriptions as $sub)
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $sub->project?->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $sub->package_name }} · {{ strtoupper($sub->currency) }} {{ number_format((float) ($sub->monthly_fee ?? 0), 2) }}/{{ $sub->billing_cycle }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800">{{ $sub->product_status }}</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800">{{ $sub->license_status }}</span>
                </div>
            </div>
            <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-gray-500">{{ __('Renewal') }}</dt><dd>{{ $sub->renewal_date?->toFormattedDateString() ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Contract') }}</dt><dd class="capitalize">{{ str_replace('_', ' ', $sub->contract_status) }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Version') }}</dt><dd>{{ $sub->versionTracking?->current_version ?? __('Not recorded') }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Server') }}</dt><dd>{{ $sub->infrastructure?->server?->name ?? __('Not assigned') }}</dd></div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('tenants.show', $tenant).'?tab=modules&subscription='.$sub->id }}" class="rounded-lg border border-indigo-200 px-2 py-1 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:text-indigo-300">{{ __('Modules') }}</a>
                <a href="{{ route('tenants.show', $tenant).'?tab=infrastructure&subscription='.$sub->id }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Infrastructure') }}</a>
                <a href="{{ route('tenants.show', $tenant).'?tab=versions&subscription='.$sub->id }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Versions') }}</a>
                <a href="{{ route('tenants.show', $tenant).'?tab=documents&subscription='.$sub->id }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Documents') }}</a>
                <a href="{{ route('tenants.show', $tenant).'?tab=integrations&subscription='.$sub->id }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Integrations') }}</a>
                <form method="post" action="{{ route('tenants.project-subscriptions.license.activate', [$tenant, $sub]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Activate') }}</button></form>
                <form method="post" action="{{ route('tenants.project-subscriptions.license.suspend', [$tenant, $sub]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Suspend') }}</button></form>
                <form method="post" action="{{ route('tenants.project-subscriptions.license.disable', [$tenant, $sub]) }}">@csrf<button type="submit" class="rounded-lg border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700">{{ __('Disable') }}</button></form>
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">{{ __('No hosted projects linked. Add a subscription below.') }}</p>
    @endforelse

    <form method="post" action="{{ route('tenants.project-subscriptions.store', $tenant) }}" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
        @csrf
        <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ __('Add project subscription') }}</p>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <select name="project_id" required class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="">{{ __('Select project') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
            <input name="package_name" placeholder="{{ __('Package name') }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <input name="monthly_fee" type="number" step="0.01" min="0" placeholder="{{ __('Monthly fee') }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">{{ __('Add') }}</button>
        </div>
    </form>
</div>
