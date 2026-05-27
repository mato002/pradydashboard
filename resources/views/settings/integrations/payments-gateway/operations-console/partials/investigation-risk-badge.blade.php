@if ($resource)
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <x-ui.status-badge :variant="$riskVariant($riskLevel ?? 'medium')">{{ __('Risk: :level', ['level' => ucfirst($riskLevel ?? 'medium')]) }}</x-ui.status-badge>
        @if (($tenantImpact['scoped'] ?? false))
            <span class="text-xs text-slate-500">{{ __('Tenant-scoped incident') }}</span>
        @endif
    </div>
@endif
