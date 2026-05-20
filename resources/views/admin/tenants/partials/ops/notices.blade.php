<div class="grid gap-6 lg:grid-cols-3">
    <form method="post" action="{{ route('tenants.notices.store', $tenant) }}" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
        @csrf
        <h3 class="text-sm font-semibold">{{ __('Create notice') }}</h3>
        <div class="mt-3 space-y-2 text-sm">
            <select name="notice_type" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($noticeTypes as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
            </select>
            <input name="title" required placeholder="{{ __('Title') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
            <textarea name="message" rows="4" required placeholder="{{ __('Message') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"></textarea>
            <select name="severity" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($noticeSeverities as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
            </select>
            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                @foreach ($noticeStatuses as $v => $l) <option value="{{ $v }}">{{ $l }}</option> @endforeach
            </select>
            <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900" />
            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white">{{ __('Save notice') }}</button>
        </div>
    </form>

    <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold">{{ __('Notices') }}</h3>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($supportOps['recent_notices'] as $notice)
                <li class="px-4 py-3 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-medium">{{ $notice->title }}</p>
                            <p class="text-xs text-gray-500 capitalize">
                                {{ $noticeTypes[$notice->notice_type] ?? $notice->notice_type }}
                                · {{ $noticeSeverities[$notice->severity] ?? $notice->severity }}
                                · {{ $noticeStatuses[$notice->status] ?? $notice->status }}
                            </p>
                        </div>
                        @if ($notice->status === 'draft')
                            <form method="post" action="{{ route('tenants.notices.send', [$tenant, $notice]) }}">
                                @csrf
                                <button type="submit" class="rounded border px-2 py-1 text-xs font-semibold">{{ __('Mark sent') }}</button>
                            </form>
                        @elseif ($notice->sent_at)
                            <span class="text-xs text-gray-500">{{ $notice->sent_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $notice->message }}</p>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No notices yet.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
