<x-dashboard-layout :heading="__('Add department')" :subheading="__('HR')">
    <form method="post" action="{{ route('hr.departments.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @include('admin.hr.departments._form', ['department' => $department, 'managers' => $managers, 'statuses' => $statuses])
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Create') }}</button>
    </form>
</x-dashboard-layout>
