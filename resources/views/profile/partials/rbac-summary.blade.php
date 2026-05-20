@php
    $activeRoleService = app(\App\Domain\Rbac\ActiveRoleService::class);
    $active = $activeRoleService->getActiveAssignment($user);
    $assignments = $user->roleAssignments()->with('role')->latest()->get();
    $switchLogs = $user->roleSwitchLogs()->latest('created_at')->limit(10)->get();
@endphp

<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60 sm:p-8">
    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Roles & access') }}</h3>
    <p class="mt-1 text-sm text-slate-500">{{ __('Active role:') }} <strong>{{ $active?->role?->name ?? __('None') }}</strong></p>

    <h4 class="mt-6 text-sm font-semibold">{{ __('Assigned roles') }}</h4>
    <ul class="mt-2 space-y-2 text-sm">
        @forelse ($assignments as $assignment)
            <li class="rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                <span class="font-medium">{{ $assignment->role?->name }}</span>
                <span class="text-slate-500">— {{ $assignment->scopeLabel() }}</span>
                <span class="block text-xs text-slate-500">{{ __('Status') }}: {{ $assignment->status->value }}
                    @if ($assignment->expires_at) · {{ __('Expires') }} {{ $assignment->expires_at->format('Y-m-d H:i') }}@endif
                </span>
            </li>
        @empty
            <li class="text-slate-500">{{ __('No role assignments.') }}</li>
        @endforelse
    </ul>

    <h4 class="mt-6 text-sm font-semibold">{{ __('Recent role switches') }}</h4>
    <ul class="mt-2 space-y-1 text-xs text-slate-600 dark:text-slate-400">
        @forelse ($switchLogs as $log)
            <li>{{ $log->created_at?->format('Y-m-d H:i') }}: {{ $log->from_role_name ?? '—' }} → {{ $log->to_role_name }}</li>
        @empty
            <li>{{ __('No switches recorded yet.') }}</li>
        @endforelse
    </ul>
</div>
