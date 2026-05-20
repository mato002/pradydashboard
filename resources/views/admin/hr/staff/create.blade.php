<x-dashboard-layout :heading="__('Add staff')" :subheading="__('HR')">
    <form method="post" action="{{ route('hr.staff.store') }}" class="max-w-3xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @include('admin.hr.staff._form')
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Create staff profile') }}</button>
    </form>
</x-dashboard-layout>
