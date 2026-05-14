<x-dashboard-layout :heading="__('New server')">
    <form method="post" action="{{ route('servers.store') }}" class="max-w-4xl space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @include('admin.servers._form', ['server' => $server])
        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <a href="{{ route('servers.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-dashboard-layout>
