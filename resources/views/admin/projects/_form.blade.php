@php
    $formOptions = $formOptions ?? \App\Support\ProjectFormOptions::all();
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y';
    $checkWrap = 'flex items-start gap-3 rounded-xl border border-slate-200/80 px-4 py-3 dark:border-slate-700';
    $bool = fn (string $field, bool $default = false) => (bool) old($field, $project->{$field} ?? $default);
@endphp

<div
    x-data="{ section: 'basic' }"
    class="space-y-5"
>
    <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200/80 bg-white p-2 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
        @foreach ([
            'basic' => __('Basic'),
            'business' => __('Business'),
            'license' => __('License'),
            'infrastructure' => __('Infrastructure'),
            'hosting' => __('Hosting & stack'),
        ] as $key => $label)
            <button
                type="button"
                @click="section = '{{ $key }}'"
                :class="section === '{{ $key }}' ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                class="rounded-xl px-3 py-2 text-xs font-semibold transition"
            >{{ $label }}</button>
        @endforeach
    </div>

    <div x-show="section === 'basic'" x-cloak class="space-y-5">
        <x-admin.form-section :title="__('Product identity')" :description="__('Name, codes, lifecycle, and ownership.')">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="name" :value="__('Project name')" />
                    <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $project->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="system_code" :value="__('System code / slug')" />
                    <x-text-input id="system_code" name="system_code" type="text" :class="$inputClass.' font-mono'" :value="old('system_code', $project->system_code)" />
                    <p class="mt-1.5 text-xs text-slate-500">{{ __('Lowercase letters, numbers, hyphens. Used internally across ops.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('system_code')" />
                </div>
                <div>
                    <x-input-label for="product_slug" :value="__('Product slug (license API)')" />
                    <x-text-input id="product_slug" name="product_slug" type="text" :class="$inputClass.' font-mono'" :value="old('product_slug', $project->product_slug)" />
                    <p class="mt-1.5 text-xs text-slate-500">{{ __('Sent as product in POST /api/license/check.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('product_slug')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="domain" :value="__('Domain / subdomain')" />
                    <x-text-input id="domain" name="domain" type="text" :class="$inputClass.' font-mono'" :value="old('domain', $project->domain)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('domain')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="{{ $textareaClass }}">{{ old('description', $project->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="{{ $selectClass }}" required>
                        @foreach ($formOptions['status'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $project->status ?? 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
                <div>
                    <x-input-label for="owner_department" :value="__('Owner / department')" />
                    <x-text-input id="owner_department" name="owner_department" type="text" :class="$inputClass" :value="old('owner_department', $project->owner_department)" />
                    <x-input-error class="mt-2" :messages="$errors->get('owner_department')" />
                </div>
                <div>
                    <x-input-label for="version" :value="__('Current version')" />
                    <x-text-input id="version" name="version" type="text" :class="$inputClass.' font-mono'" :value="old('version', $project->version)" />
                    <x-input-error class="mt-2" :messages="$errors->get('version')" />
                </div>
                <div>
                    <x-input-label for="min_supported_version" :value="__('Minimum supported version')" />
                    <x-text-input id="min_supported_version" name="min_supported_version" type="text" :class="$inputClass.' font-mono'" :value="old('min_supported_version', $project->min_supported_version)" />
                    <x-input-error class="mt-2" :messages="$errors->get('min_supported_version')" />
                </div>
                <div>
                    <x-input-label for="latest_release_date" :value="__('Latest release date')" />
                    <x-text-input id="latest_release_date" name="latest_release_date" type="date" :class="$inputClass" :value="old('latest_release_date', optional($project->latest_release_date)->format('Y-m-d'))" />
                    <x-input-error class="mt-2" :messages="$errors->get('latest_release_date')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="internal_notes" :value="__('Internal notes')" />
                    <textarea id="internal_notes" name="internal_notes" rows="3" class="{{ $textareaClass }}">{{ old('internal_notes', $project->internal_notes) }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500">{{ __('Ops-only notes; not shown to tenants.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('internal_notes')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="notes" :value="__('General notes')" />
                    <textarea id="notes" name="notes" rows="2" class="{{ $textareaClass }}">{{ old('notes', $project->notes) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
            </div>
        </x-admin.form-section>
    </div>

    <div x-show="section === 'business'" x-cloak class="space-y-5">
        <x-admin.form-section :title="__('Commercial defaults')" :description="__('Default pricing and contract terms for new tenant subscriptions.')">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="business_model" :value="__('Business model')" />
                    <select id="business_model" name="business_model" class="{{ $selectClass }}">
                        <option value="">{{ __('Not set') }}</option>
                        @foreach ($formOptions['business_model'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('business_model', $project->business_model) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('business_model')" />
                </div>
                <div>
                    <x-input-label for="deployment_type" :value="__('Technical deployment type')" />
                    <select id="deployment_type" name="deployment_type" class="{{ $selectClass }}">
                        <option value="">{{ __('Not set') }}</option>
                        @foreach ($formOptions['deployment_type'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('deployment_type', $project->deployment_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('deployment_type')" />
                </div>
                <div>
                    <x-input-label for="billing_model" :value="__('Billing model')" />
                    <select id="billing_model" name="billing_model" class="{{ $selectClass }}">
                        <option value="">{{ __('Not set') }}</option>
                        @foreach ($formOptions['billing_model'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('billing_model', $project->billing_model) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('billing_model')" />
                </div>
                <div>
                    <x-input-label for="currency" :value="__('Currency')" />
                    <select id="currency" name="currency" class="{{ $selectClass }}" required>
                        @foreach ($formOptions['currency'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('currency', $project->currency ?? 'KES') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                </div>
                <div>
                    <x-input-label for="default_setup_fee" :value="__('Default setup fee')" />
                    <x-text-input id="default_setup_fee" name="default_setup_fee" type="number" step="0.01" min="0" :class="$inputClass" :value="old('default_setup_fee', $project->default_setup_fee)" />
                    <x-input-error class="mt-2" :messages="$errors->get('default_setup_fee')" />
                </div>
                <div>
                    <x-input-label for="default_monthly_fee" :value="__('Default monthly fee')" />
                    <x-text-input id="default_monthly_fee" name="default_monthly_fee" type="number" step="0.01" min="0" :class="$inputClass" :value="old('default_monthly_fee', $project->default_monthly_fee)" />
                    <x-input-error class="mt-2" :messages="$errors->get('default_monthly_fee')" />
                </div>
                <div>
                    <x-input-label for="trial_days" :value="__('Trial days')" />
                    <x-text-input id="trial_days" name="trial_days" type="number" min="0" :class="$inputClass" :value="old('trial_days', $project->trial_days)" />
                    <x-input-error class="mt-2" :messages="$errors->get('trial_days')" />
                </div>
                <div>
                    <x-input-label for="minimum_contract_term" :value="__('Minimum contract term (months)')" />
                    <x-text-input id="minimum_contract_term" name="minimum_contract_term" type="number" min="0" :class="$inputClass" :value="old('minimum_contract_term', $project->minimum_contract_term)" />
                    <x-input-error class="mt-2" :messages="$errors->get('minimum_contract_term')" />
                </div>
                <div>
                    <x-input-label for="monthly_revenue" :value="__('Monthly revenue (platform)')" />
                    <x-text-input id="monthly_revenue" name="monthly_revenue" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_revenue', $project->monthly_revenue)" />
                    <x-input-error class="mt-2" :messages="$errors->get('monthly_revenue')" />
                </div>
                <div>
                    <x-input-label for="monthly_cost" :value="__('Monthly cost (platform)')" />
                    <x-text-input id="monthly_cost" name="monthly_cost" type="number" step="0.01" min="0" :class="$inputClass" :value="old('monthly_cost', $project->monthly_cost)" />
                    <x-input-error class="mt-2" :messages="$errors->get('monthly_cost')" />
                </div>
            </div>
        </x-admin.form-section>
    </div>

    <div x-show="section === 'license'" x-cloak class="space-y-5">
        <x-admin.form-section :title="__('License rules')" :description="__('How tenant licenses are validated and enforced.')">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="license_validation_mode" :value="__('License validation mode')" />
                    <select id="license_validation_mode" name="license_validation_mode" class="{{ $selectClass }}" required>
                        @foreach ($formOptions['license_validation_mode'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('license_validation_mode', $project->license_validation_mode ?? 'api') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('license_validation_mode')" />
                </div>
                <div>
                    <x-input-label for="grace_period_days" :value="__('Grace period (days)')" />
                    <x-text-input id="grace_period_days" name="grace_period_days" type="number" min="0" :class="$inputClass" :value="old('grace_period_days', $project->grace_period_days ?? 7)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('grace_period_days')" />
                </div>
                <div class="md:col-span-2 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        'kill_switch_allowed' => [__('Kill switch allowed'), __('Remote disable when billing or contract fails.')],
                        'offline_mode_allowed' => [__('Offline mode allowed'), __('Tenant may run without live API checks.')],
                        'contract_document_required' => [__('Contract document required'), __('Signed contract must be on file before go-live.')],
                    ] as $field => [$label, $help])
                        <label class="{{ $checkWrap }}">
                            <input type="hidden" name="{{ $field }}" value="0" />
                            <input type="checkbox" name="{{ $field }}" value="1" class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($bool($field)) />
                            <span>
                                <span class="block text-sm font-medium text-slate-900 dark:text-slate-100">{{ $label }}</span>
                                <span class="block text-xs text-slate-500">{{ $help }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </x-admin.form-section>
    </div>

    <div x-show="section === 'infrastructure'" x-cloak class="space-y-5">
        <x-admin.form-section :title="__('Infrastructure requirements')" :description="__('What must be provisioned for each tenant on this product.')">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'requires_server' => [__('Requires server'), true],
                    'requires_domain' => [__('Requires domain'), true],
                    'requires_ssl' => [__('Requires SSL'), true],
                    'requires_whm' => [__('Requires WHM/cPanel'), false],
                    'default_database_required' => [__('Default database required'), true],
                    'backup_required' => [__('Backup required'), true],
                ] as $field => [$label, $default])
                    <label class="{{ $checkWrap }}">
                        <input type="hidden" name="{{ $field }}" value="0" />
                        <input type="checkbox" name="{{ $field }}" value="1" class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($bool($field, $default)) />
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="default_disk_quota_mb" :value="__('Default disk quota (MB)')" />
                    <x-text-input id="default_disk_quota_mb" name="default_disk_quota_mb" type="number" min="0" :class="$inputClass" :value="old('default_disk_quota_mb', $project->default_disk_quota_mb)" />
                    <x-input-error class="mt-2" :messages="$errors->get('default_disk_quota_mb')" />
                </div>
            </div>
        </x-admin.form-section>
    </div>

    <div x-show="section === 'hosting'" x-cloak class="space-y-5">
        <x-admin.form-section :title="__('Hosting & stack')" :description="__('Server assignment, stack, and repository.')">
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
                    <textarea id="technology_stack" name="technology_stack" rows="2" class="{{ $textareaClass }}">{{ old('technology_stack', $project->technology_stack) }}</textarea>
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
    </div>
</div>
