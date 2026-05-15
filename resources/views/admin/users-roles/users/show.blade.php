@php
    $statusVariant = match ($profile['status'] ?? 'active') {
        'active' => 'success',
        'invited' => 'warning',
        default => 'danger',
    };
@endphp

<x-dashboard-layout :heading="$profile['name']" :subheading="$profile['email']">
    <x-admin.form-shell
        :title="$profile['name']"
        :subtitle="$profile['email']"
        :badge="__('User profile')"
        :back-href="route('users-roles.index')"
        :back-label="__('Back to IAM center')"
    >
        <x-slot name="actions">
            <a href="{{ route('users-roles.users.edit', $userRef) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                {{ __('Edit') }}
            </a>
        </x-slot>

        <div class="grid gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-bold text-white">{{ $profile['initials'] }}</span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.status-badge :variant="$statusVariant">{{ ucfirst($profile['status']) }}</x-ui.status-badge>
                            @if ($profile['mfa'] ?? false)
                                <x-ui.status-badge variant="success">{{ __('MFA on') }}</x-ui.status-badge>
                            @else
                                <x-ui.status-badge variant="warning">{{ __('MFA off') }}</x-ui.status-badge>
                            @endif
                            @if (($profile['risk'] ?? 'low') === 'high')
                                <x-ui.status-badge variant="danger">{{ __('High risk') }}</x-ui.status-badge>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $profile['department'] }} · {{ implode(', ', $profile['roles'] ?? []) }}</p>
                    </div>
                </div>

                <x-admin.form-section :title="__('Recent audit events')">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($auditLogs as $log)
                            <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                <span class="font-mono text-xs text-slate-500">{{ $log['time'] }}</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $log['action'] }}</span>
                                <span class="text-xs text-slate-500">{{ $log['target'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-admin.form-section>
            </div>

            <div class="space-y-4">
                <dl class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-sm shadow-card dark:border-slate-800/80 dark:bg-slate-900/60">
                    <div class="border-b border-slate-200/80 px-5 py-3 dark:border-slate-800/80">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ __('Access') }}</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Access level') }}</dt><dd class="font-medium capitalize">{{ $profile['access_level'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Sessions') }}</dt><dd class="font-medium">{{ $profile['sessions'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last activity') }}</dt><dd class="font-medium">{{ $profile['last_activity'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Last IP') }}</dt><dd class="font-mono text-xs">{{ $profile['last_ip'] }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Location') }}</dt><dd class="font-medium">{{ $profile['location'] }}</dd></div>
                    </div>
                </dl>

                <x-admin.form-section :title="__('Active sessions')">
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                        @foreach (array_slice($sessions, 0, 3) as $session)
                            <li class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/80">
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $session['device'] }}</span>
                                <span class="block text-slate-500">{{ $session['browser'] }} · {{ $session['ip'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-admin.form-section>
            </div>
        </div>
    </x-admin.form-shell>
</x-dashboard-layout>
