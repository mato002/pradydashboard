<div class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add provider notice') }}</h3>
    <form method="post" action="{{ route('servers.notices.store', $server) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
        @csrf
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Source / provider') }}</label>
            <input type="text" name="source" value="{{ old('source', $server->provider) }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Type') }}</label>
            <select name="notice_type" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                @foreach (\App\Models\ServerProviderNotice::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('notice_type') === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Message') }}</label>
            <textarea name="body" rows="2" class="mt-1.5 w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">{{ old('body') }}</textarea>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Severity') }}</label>
            <select name="severity" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                @foreach (\App\Models\ServerProviderNotice::SEVERITIES as $sev)
                    <option value="{{ $sev }}">{{ ucfirst($sev) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Status') }}</label>
            <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                @foreach (\App\Models\ServerProviderNotice::STATUSES as $st)
                    <option value="{{ $st }}" @selected($st === 'open')>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Notice date') }}</label>
            <input type="date" name="notice_date" value="{{ old('notice_date', now()->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Due date') }}</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Email / reference') }}</label>
            <input type="text" name="source_reference" value="{{ old('source_reference') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Attachment ref') }}</label>
            <input type="text" name="attachment_reference" value="{{ old('attachment_reference') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save notice') }}</button>
        </div>
    </form>
</div>
