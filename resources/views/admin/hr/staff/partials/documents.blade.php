<div class="grid gap-6 lg:grid-cols-2">
    <form method="post" action="{{ route('hr.staff.documents.store', $staff) }}" enctype="multipart/form-data" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        <h3 class="text-sm font-semibold">{{ __('Upload document') }}</h3>
        <div class="mt-3 space-y-3 text-sm">
            <input name="title" required placeholder="{{ __('Title') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            <select name="document_type" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($documentTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="file" name="file" required class="w-full text-sm" />
            <div class="grid grid-cols-2 gap-2">
                <input type="date" name="signed_date" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                <input type="date" name="expiry_date" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            </div>
            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white">{{ __('Upload') }}</button>
        </div>
    </form>

    <ul class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
        @forelse ($staff->documents as $doc)
            <li class="flex flex-wrap items-start justify-between gap-2 px-4 py-3 text-sm">
                <div>
                    <p class="font-medium">{{ $doc->title }}</p>
                    <p class="text-xs uppercase text-gray-500">{{ $documentTypes[$doc->document_type] ?? $doc->document_type }}</p>
                    @if ($doc->expiry_date)
                        <p class="text-xs text-gray-500">{{ __('Expires') }} {{ $doc->expiry_date->toFormattedDateString() }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('hr.staff.documents.download', [$staff, $doc]) }}" class="text-xs font-semibold text-indigo-600 hover:underline">{{ __('Download') }}</a>
                    <form method="post" action="{{ route('hr.staff.documents.destroy', [$staff, $doc]) }}" onsubmit="return confirm('{{ __('Delete document?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:underline">{{ __('Delete') }}</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-gray-500">{{ __('No documents uploaded.') }}</li>
        @endforelse
    </ul>
</div>
