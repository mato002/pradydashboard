@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="subject" :value="__('Subject')" />
        <x-text-input id="subject" name="subject" type="text" :class="$inputClass" :value="old('subject', $ticket->subject)" required />
        <x-input-error class="mt-2" :messages="$errors->get('subject')" />
    </div>
    <div>
        <x-input-label for="tenant_id" :value="__('Tenant')" />
        <select id="tenant_id" name="tenant_id" class="{{ $selectClass }}">
            <option value="">{{ __('Unassigned') }}</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $ticket->tenant_id) == $tenant->id)>{{ $tenant->company_name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('tenant_id')" />
    </div>
    <div>
        <x-input-label for="project_id" :value="__('Project')" />
        <select id="project_id" name="project_id" class="{{ $selectClass }}">
            <option value="">{{ __('None') }}</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', $ticket->project_id) == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
    </div>
    <div>
        <x-input-label for="priority" :value="__('Priority')" />
        <select id="priority" name="priority" class="{{ $selectClass }}" required>
            @foreach (['low', 'medium', 'high', 'critical'] as $p)
                <option value="{{ $p }}" @selected(old('priority', $ticket->priority ?? 'medium') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
    </div>
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="{{ $selectClass }}" required>
            @foreach (['open', 'pending', 'in_progress', 'escalated', 'resolved', 'closed'] as $s)
                <option value="{{ $s }}" @selected(old('status', $ticket->status ?? 'open') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>
</div>
