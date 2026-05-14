<x-dashboard-layout :heading="__('Edit hosted project')">
    <form method="post" action="{{ route('projects.update', $project) }}" class="max-w-4xl space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('put')
        @include('admin.projects._form', ['project' => $project, 'servers' => $servers])
        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Update') }}</x-primary-button>
            <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-dashboard-layout>
