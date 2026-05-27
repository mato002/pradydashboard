@if (($riskOpsCenter['total'] ?? 0) > 0)
    <x-admin.operations-risk-center :center="$riskOpsCenter" />
@endif
