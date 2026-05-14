<x-dashboard-layout :heading="__('Edit tenant')">
    <form method="post" action="{{ route('tenants.update', $tenant) }}" class="max-w-4xl space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('put')
        @if (request()->filled('return_tab'))
            <input type="hidden" name="return_tab" value="{{ request('return_tab') }}" />
        @endif
        @include('admin.tenants._form', ['tenant' => $tenant, 'projects' => $projects, 'servers' => $servers])
        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Update') }}</x-primary-button>
            <a href="{{ route('tenants.show', $tenant).(request()->filled('return_tab') ? '?tab='.urlencode((string) request('return_tab')) : '') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-dashboard-layout>
