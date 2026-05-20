@auth
    @php
        $active = $rbacActiveAssignment ?? null;
        $activeId = $active?->id;
        $options = $rbacActivatableAssignments ?? [];
    @endphp
    <div class="relative" x-data="{ open: false }">
        <button
            type="button"
            class="inline-flex max-w-[14rem] items-center gap-2 rounded-xl border border-indigo-200/80 bg-indigo-50/80 px-3 py-2 text-xs font-semibold text-indigo-900 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-100"
            @click="open = !open"
            title="{{ __('Switch active role') }}"
        >
            <span class="truncate text-indigo-600/80 dark:text-indigo-300/80">{{ __('Current Role') }}:</span>
            <span class="truncate">{{ $active?->role?->name ?? __('None — select a role') }}</span>
            @if ($active?->expires_at)
                <span class="hidden shrink-0 text-[10px] text-amber-600 lg:inline" title="{{ __('Assignment expires') }}">⏱</span>
            @endif
            <svg class="h-4 w-4 shrink-0 text-indigo-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
        <div
            x-show="open"
            @click.outside="open = false"
            x-cloak
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200/80 bg-white py-1 shadow-card dark:border-slate-700 dark:bg-slate-900"
        >
            @if ($active)
                <p class="border-b border-slate-100 px-3 py-2 text-[11px] text-slate-500 dark:border-slate-800">
                    {{ __('Active') }}: {{ $active->scopeLabel() }}
                    @if ($active->expires_at)
                        · {{ __('Expires') }} {{ $active->expires_at->format('M j, Y H:i') }}
                    @endif
                </p>
            @endif
            @forelse ($options as $assignment)
                @if ($assignment->id !== $activeId)
                    <form method="POST" action="{{ route('active-role.switch') }}" class="border-b border-slate-100 px-3 py-2 last:border-0 dark:border-slate-800">
                        @csrf
                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                        @if ($assignment->role?->requires_elevation)
                            <input type="password" name="password" required autocomplete="current-password" placeholder="{{ __('Password to activate') }}" class="mb-2 w-full rounded-lg border px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-950">
                            <p class="mb-2 text-[10px] text-slate-500">{{ __('Password required. OTP/MFA coming later.') }}</p>
                        @endif
                        <button type="submit" class="w-full text-left hover:opacity-90">
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $assignment->role?->name }}</span>
                            <span class="block text-[11px] text-slate-500">{{ $assignment->scopeLabel() }}</span>
                            @if ($assignment->expires_at)
                                <span class="block text-[11px] text-amber-600">{{ __('Expires') }}: {{ $assignment->expires_at->format('M j, Y H:i') }}</span>
                            @endif
                        </button>
                    </form>
                @endif
            @empty
                <p class="px-3 py-2 text-xs text-slate-500">{{ __('No other activatable role assignments.') }}</p>
            @endforelse
        </div>
    </div>
@endauth
