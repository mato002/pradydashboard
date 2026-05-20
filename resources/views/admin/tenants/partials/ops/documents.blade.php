@php
    use App\Domain\Tenancy\OperationalDocumentInsights;
    $documentsUrl = fn (?int $subscriptionId = null) => route('tenants.show', array_filter([
        'tenant' => $tenant,
        'tab' => 'documents',
        'subscription' => $subscriptionId,
    ]));
    $editingDocument = request()->filled('edit')
        ? $tenant->operationalDocuments->firstWhere('id', (int) request('edit'))
        : null;
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900';
@endphp

@if ($missingContractWarnings->isNotEmpty())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-100">
        <p class="font-semibold">{{ __('Missing required contract') }}</p>
        <ul class="mt-1 list-inside list-disc text-xs">
            @foreach ($missingContractWarnings as $warning)
                <li>{{ $warning['project_name'] }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($expiringDocuments->isNotEmpty())
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
        <p class="font-semibold">{{ __('Expiring documents') }} ({{ OperationalDocumentInsights::EXPIRY_WARNING_DAYS }} {{ __('days') }})</p>
        <ul class="mt-1 space-y-1 text-xs">
            @foreach ($expiringDocuments as $doc)
                <li>
                    {{ $doc->title }}
                    — {{ $doc->expiry_date?->toFormattedDateString() }}
                    @if ($doc->isExpired()) <span class="font-semibold">({{ __('Expired') }})</span> @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Operational documents for tenant, project subscription, or project scope.') }}</p>
    </div>
    <div class="min-w-[14rem]">
        <label class="block text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Filter by subscription') }}</label>
        <select class="{{ $inputClass }}" onchange="window.location.href = this.value">
            <option value="{{ $documentsUrl() }}" @selected(! request()->filled('subscription'))>{{ __('All documents') }}</option>
            @foreach ($tenant->projectSubscriptions as $sub)
                <option value="{{ $documentsUrl($sub->id) }}" @selected((int) request('subscription') === $sub->id)>
                    {{ $sub->project?->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $editingDocument ? __('Edit document') : __('Upload document') }}</h3>
    <form
        method="post"
        action="{{ $editingDocument ? route('tenants.documents.update', [$tenant, $editingDocument]) : route('tenants.documents.store', $tenant) }}"
        enctype="multipart/form-data"
        class="mt-4 grid gap-4 md:grid-cols-2"
    >
        @csrf
        @if ($editingDocument)
            @method('put')
        @endif
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">{{ __('Title') }}</label>
            <input type="text" name="title" value="{{ old('title', $editingDocument?->title) }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Document type') }}</label>
            <select name="document_type" required class="{{ $inputClass }}">
                @foreach ($documentTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('document_type', $editingDocument?->document_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Status') }}</label>
            <select name="status" required class="{{ $inputClass }}">
                @foreach ($documentStatusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $editingDocument?->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Project subscription') }}</label>
            <select name="tenant_project_subscription_id" class="{{ $inputClass }}">
                <option value="">{{ __('Tenant-wide / none') }}</option>
                @foreach ($tenant->projectSubscriptions as $sub)
                    <option value="{{ $sub->id }}" @selected(old('tenant_project_subscription_id', $editingDocument?->tenant_project_subscription_id ?? request('subscription')) == $sub->id)>
                        {{ $sub->project?->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Project') }}</label>
            <select name="project_id" class="{{ $inputClass }}">
                <option value="">{{ __('Auto from subscription') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected(old('project_id', $editingDocument?->project_id ?? $tenant->project_id) == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Signed date') }}</label>
            <input type="date" name="signed_date" value="{{ old('signed_date', optional($editingDocument?->signed_date)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Expiry date') }}</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($editingDocument?->expiry_date)->format('Y-m-d')) }}" class="{{ $inputClass }}" />
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">{{ __('File') }} @if($editingDocument)<span class="text-xs text-gray-500">({{ __('leave empty to keep current') }})</span>@endif</label>
            <input type="file" name="file" @unless($editingDocument) required @endunless class="{{ $inputClass }}" />
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">{{ __('Notes') }}</label>
            <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $editingDocument?->notes) }}</textarea>
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-2">
            <x-primary-button>{{ $editingDocument ? __('Update document') : __('Upload document') }}</x-primary-button>
            @if ($editingDocument)
                <a href="{{ $documentsUrl($editingDocument->tenant_project_subscription_id) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400">{{ __('Cancel edit') }}</a>
            @endif
        </div>
    </form>
</div>

@forelse ($filteredDocuments as $doc)
    @php $isExpiring = $expiringDocuments->contains('id', $doc->id); @endphp
    <div @class([
        'mb-3 rounded-xl border p-4 text-sm',
        'border-amber-300 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20' => $isExpiring,
        'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' => ! $isExpiring,
    ])>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $doc->title }}</p>
                <p class="text-xs text-gray-500">{{ $doc->typeLabel() }} · {{ $doc->statusLabel() }}</p>
                @if ($doc->subscription)
                    <p class="mt-1 text-xs text-gray-500">{{ __('Subscription') }}: {{ $doc->subscription->project?->name }}</p>
                @elseif ($doc->project)
                    <p class="mt-1 text-xs text-gray-500">{{ __('Project') }}: {{ $doc->project->name }}</p>
                @endif
                <p class="mt-1 text-xs text-gray-500">
                    {{ __('Signed') }}: {{ $doc->signed_date?->toFormattedDateString() ?? '—' }}
                    · {{ __('Expires') }}: {{ $doc->expiry_date?->toFormattedDateString() ?? '—' }}
                </p>
                @if ($isExpiring)
                    <p class="mt-1 text-xs font-semibold text-amber-800 dark:text-amber-200">{{ $doc->isExpired() ? __('Expired') : __('Expiring soon') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tenants.documents.download', [$tenant, $doc]) }}" class="rounded-lg border px-2 py-1 text-xs font-semibold text-indigo-600">{{ __('Download') }}</a>
                <a href="{{ route('tenants.show', array_filter(['tenant' => $tenant, 'tab' => 'documents', 'subscription' => $doc->tenant_project_subscription_id, 'edit' => $doc->id])) }}" class="rounded-lg border px-2 py-1 text-xs font-semibold">{{ __('Edit') }}</a>
                <form method="post" action="{{ route('tenants.documents.destroy', [$tenant, $doc]) }}" onsubmit="return confirm(@json(__('Delete this document?')));">
                    @csrf
                    @method('delete')
                    <button type="submit" class="rounded-lg border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700">
        {{ __('No operational documents uploaded yet.') }}
    </p>
@endforelse
