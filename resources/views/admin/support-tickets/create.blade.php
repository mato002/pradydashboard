<x-dashboard-layout :heading="__('New ticket')" :subheading="__('Log a customer support request')">
    <x-admin.form-shell
        :title="__('Create support ticket')"
        :subtitle="__('Assign tenant, priority, and routing for the support queue.')"
        :badge="__('Support')"
        :back-href="route('support-tickets.index')"
        :back-label="__('Back to queue')"
    >
        <form method="post" action="{{ route('support-tickets.store') }}" class="max-w-4xl space-y-5">
            @csrf
            <x-admin.form-section :title="__('Ticket details')" :description="__('Subject, tenant context, and workflow state.')">
                @include('admin.support-tickets._form', ['ticket' => $ticket, 'tenants' => $tenants, 'projects' => $projects])
            </x-admin.form-section>
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                    {{ __('Create ticket') }}
                </button>
                <a href="{{ route('support-tickets.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
