@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y';
@endphp

<div class="space-y-5">
    <x-admin.form-section :title="__('Product identity')" :description="__('Hosted application name, domain, and license API slug.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="name" :value="__('Project name')" />
                <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $project->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="domain" :value="__('Domain / subdomain')" />
                <x-text-input id="domain" name="domain" type="text" :class="$inputClass.' font-mono'" :value="old('domain', $project->domain)" placeholder="property.pradytecai.com" required />
                <x-input-error class="mt-2" :messages="$errors->get('domain')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="product_slug" :value="__('Product slug (license API)')" />
                <x-text-input id="product_slug" name="product_slug" type="text" :class="$inputClass.' font-mono'" :value="old('product_slug', $project->product_slug)" placeholder="property_management" />
                <p class="mt-1.5 text-xs text-slate-500">{{ __('Lowercase letters, numbers, underscores. Sent as product in POST /api/license/check.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('product_slug')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Infrastructure')" :description="__('Server assignment, stack, and repository.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="server_id" :value="__('Server')" />
                <select id="server_id" name="server_id" class="{{ $selectClass }}">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($servers as $srv)
                        <option value="{{ $srv->id }}" @selected(old('server_id', $project->server_id) == $srv->id)>{{ $srv->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('server_id')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="technology_stack" :value="__('Technology stack')" />
                <textarea id="technology_stack" name="technology_stack" rows="2" class="{{ $textareaClass }}" placeholder="Laravel 11, MySQL, Redis…">{{ old('technology_stack', $project->technology_stack) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('technology_stack')" />
            </div>
            <div>
                <x-input-label for="git_repository" :value="__('Git repository')" />
                <x-text-input id="git_repository" name="git_repository" type="url" :class="$inputClass" :value="old('git_repository', $project->git_repository)" />
                <x-input-error class="mt-2" :messages="$errors->get('git_repository')" />
            </div>
            <div>
                <x-input-label for="database_name" :value="__('Database name')" />
                <x-text-input id="database_name" name="database_name" type="text" :class="$inputClass.' font-mono'" :value="old('database_name', $project->database_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('database_name')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Lifecycle & revenue')" :description="__('Status, version, and monthly economics.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" class="{{ $selectClass }}">
                    @foreach (['active', 'maintenance', 'suspended'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $project->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>
            <div>
                <x-input-label for="version" :value="__('Version')" />
                <x-text-input id="version" name="version" type="text" :class="$inputClass.' font-mono'" :value="old('version', $project->version)" />
                <x-input-error class="mt-2" :messages="$errors->get('version')" />
            </div>
            <div>
                <x-input-label for="monthly_revenue" :value="__('Monthly revenue')" />
                <x-text-input id="monthly_revenue" name="monthly_revenue" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_revenue', $project->monthly_revenue)" />
                <x-input-error class="mt-2" :messages="$errors->get('monthly_revenue')" />
            </div>
            <div>
                <x-input-label for="monthly_cost" :value="__('Monthly cost')" />
                <x-text-input id="monthly_cost" name="monthly_cost" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_cost', $project->monthly_cost)" />
                <x-input-error class="mt-2" :messages="$errors->get('monthly_cost')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="{{ $textareaClass }}">{{ old('notes', $project->notes) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </div>
    </x-admin.form-section>
</div>
