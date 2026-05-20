@php
    use Illuminate\Support\Str;
@endphp
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="font-semibold text-gray-900 dark:text-white">{{ $integration->resolvedApiName() }}</p>
        <p class="text-xs text-gray-500">{{ $integration->serviceTypeLabel() }}@if($integration->provider_name) · {{ $integration->provider_name }}@endif</p>
        @if ($integration->endpoint_url)
            <p class="mt-1 truncate font-mono text-xs text-gray-500">{{ $integration->endpoint_url }}</p>
        @endif
        <p class="mt-1 text-xs">
            <span @class([
                'rounded-full px-2 py-0.5 font-semibold capitalize',
                'bg-emerald-100 text-emerald-800' => $integration->status === 'active',
                'bg-rose-100 text-rose-800' => $integration->status === 'failing',
                'bg-gray-100 text-gray-700' => ! in_array($integration->status, ['active', 'failing'], true),
            ])>{{ str_replace('_', ' ', $integration->status) }}</span>
            @if ($integration->last_response_code)
                <span class="ml-2 text-gray-500">HTTP {{ $integration->last_response_code }} · {{ $integration->last_response_time_ms }}ms</span>
            @endif
        </p>
        <p class="mt-1 text-xs text-gray-500">
            {{ __('Last check') }}: {{ $integration->last_checked_at?->diffForHumans() ?? __('Never') }}
            @if ($integration->uptime_percentage !== null) · {{ __('Uptime') }} {{ $integration->uptime_percentage }}% @endif
        </p>
        @if ($label = $integration->contractHealthLabel())
            <p class="mt-1 text-xs">
                {{ __('Contract') }}:
                <span @class([
                    'rounded-full px-2 py-0.5 font-semibold',
                    'bg-emerald-100 text-emerald-800' => $integration->contractHealth() === 'valid',
                    'bg-amber-100 text-amber-800' => $integration->contractHealth() === 'partial',
                    'bg-rose-100 text-rose-800' => $integration->contractHealth() === 'invalid',
                ])>{{ $label }}</span>
            </p>
        @endif
        @if ($integration->last_error)
            <p class="mt-1 text-xs text-rose-600">{{ Str::limit($integration->last_error, 120) }}</p>
        @endif
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="post" action="{{ route('tenants.project-subscriptions.integrations.test', [$tenant, $selectedSubscription, $integration]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold text-indigo-600">{{ __('Test') }}</button></form>
        @if ($tenantSystem)
            <form method="post" action="{{ route('tenants.project-subscriptions.integrations.pull-system-info', [$tenant, $selectedSubscription, $integration]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Pull info') }}</button></form>
            <form method="post" action="{{ route('tenants.project-subscriptions.integrations.pull-version', [$tenant, $selectedSubscription, $integration]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Version') }}</button></form>
            <form method="post" action="{{ route('tenants.project-subscriptions.integrations.pull-usage', [$tenant, $selectedSubscription, $integration]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Usage') }}</button></form>
            <form method="post" action="{{ route('tenants.project-subscriptions.integrations.heartbeat', [$tenant, $selectedSubscription, $integration]) }}">@csrf<button type="submit" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Heartbeat') }}</button></form>
        @endif
        <a href="{{ $integrationsUrl($selectedSubscription->id, $integration->id) }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Edit') }}</a>
    </div>
</div>
