@php
    $heading = __('Edit ticket');
@endphp

<x-dashboard-layout :heading="$heading" :subheading="$profile['id'] ?? ''">
    <x-admin.form-shell
        :title="$heading"
        :subtitle="$profile['subject'] ?? ''"
        :badge="__('Support')"
        :back-href="route('support-tickets.show', $reference)"
        :back-label="__('Back to ticket')"
    >
        @if ($isDemo ?? false)
            <div class="mb-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('This is a demo ticket. Saving will not persist — create a real ticket from the queue.') }}
            </div>
        @endif
        <form method="post" action="{{ route('support-tickets.update', $reference) }}" class="max-w-4xl space-y-5">
            @csrf
            @method('PUT')
            <x-admin.form-section :title="__('Ticket details')" :description="__('Update routing, priority, and resolution state.')">
                @include('admin.support-tickets._form', ['ticket' => $ticket, 'tenants' => $tenants, 'projects' => $projects])
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Save changes') }}
                </button>
                <a href="{{ route('support-tickets.show', $reference) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
        @unless ($isDemo ?? false)
            <form method="post" action="{{ route('support-tickets.destroy', $reference) }}" class="mt-3" onsubmit="return confirm('{{ __('Delete this ticket?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400">{{ __('Delete ticket') }}</button>
            </form>
        @endunless
    </x-admin.form-shell>
</x-dashboard-layout>
