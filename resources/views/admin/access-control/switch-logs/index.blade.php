<x-dashboard-layout :heading="__('Role switch logs')" :subheading="__('Audit trail of active role changes')">
    <div class="grid gap-5 xl:grid-cols-12">
        <div class="xl:col-span-3">@include('admin.access-control.partials.nav')</div>
        <div class="xl:col-span-9">
            <div class="overflow-hidden rounded-2xl border bg-white dark:border-slate-800 dark:bg-slate-900/60">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3">{{ __('When') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('From') }}</th>
                            <th>{{ __('To') }}</th>
                            <th>{{ __('IP') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-slate-800">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-4 py-3">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3">{{ $log->user?->name }}</td>
                                <td class="px-4 py-3">{{ $log->from_role_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $log->to_role_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
