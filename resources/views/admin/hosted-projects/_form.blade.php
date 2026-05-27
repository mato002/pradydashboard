@php
    $inputClass = 'mt-1.5 block w-full rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-100 dark:placeholder:text-slate-500';
    $selectClass = $inputClass;
    $textareaClass = $inputClass.' resize-y';
    $hostedProject = $hostedProject ?? $project ?? new \App\Models\HostedProject;
@endphp

<div class="space-y-5">
    <x-admin.form-section :title="__('Product & domain')" :description="__('Link this instance to a product and register its domain.')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="product_id" :value="__('Parent product')" :required="true" />
                <select id="product_id" name="product_id" class="{{ $selectClass }}" required>
                    <option value="" disabled @selected(! old('product_id', $hostedProject->product_id))>{{ __('Select a product…') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id', $hostedProject->product_id) == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="name" :value="__('Instance label')" :required="true" />
                <x-text-input id="name" name="name" type="text" :class="$inputClass" :value="old('name', $hostedProject->name)" placeholder="{{ __('e.g. MFI Production') }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="domain" :value="__('Domain / subdomain')" :required="true" />
                <x-text-input id="domain" name="domain" type="text" :class="$inputClass.' font-mono'" :value="old('domain', $hostedProject->domain)" placeholder="{{ __('e.g. mfi.pradytecai.com') }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('domain')" />
            </div>
            <div>
                <x-input-label for="environment" :value="__('Environment')" :required="true" />
                <select id="environment" name="environment" class="{{ $selectClass }}" required>
                    @foreach (['production', 'staging', 'demo', 'development'] as $env)
                        <option value="{{ $env }}" @selected(old('environment', $hostedProject->environment ?? 'production') === $env)>{{ ucfirst($env) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('environment')" />
            </div>
            <div>
                <x-input-label for="product_key" :value="__('Product key (optional override)')" />
                <x-text-input id="product_key" name="product_key" type="text" :class="$inputClass.' font-mono'" :value="old('product_key', $hostedProject->product_key)" placeholder="{{ __('e.g. mfi — defaults to product slug') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('product_key')" />
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Infrastructure')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="server_id" :value="__('Server')" />
                <select id="server_id" name="server_id" class="{{ $selectClass }}">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($servers as $srv)
                        <option value="{{ $srv->id }}" @selected(old('server_id', $hostedProject->server_id) == $srv->id)>{{ $srv->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="stack" :value="__('Stack')" />
                <textarea id="stack" name="stack" rows="2" class="{{ $textareaClass }}" placeholder="{{ __('e.g. Laravel 11, MySQL, Redis') }}">{{ old('stack', $hostedProject->stack) }}</textarea>
            </div>
            <div>
                <x-input-label for="git_repository" :value="__('Git repository')" />
                <x-text-input id="git_repository" name="git_repository" type="url" :class="$inputClass" :value="old('git_repository', $hostedProject->git_repository)" placeholder="{{ __('https://github.com/org/repo') }}" />
            </div>
            <div>
                <x-input-label for="database_name" :value="__('Database name')" />
                <x-text-input id="database_name" name="database_name" type="text" :class="$inputClass.' font-mono'" :value="old('database_name', $hostedProject->database_name)" placeholder="{{ __('e.g. prady_mfi_prod') }}" />
            </div>
            <div>
                <x-input-label for="cpanel_username" :value="__('cPanel username')" />
                <x-text-input id="cpanel_username" name="cpanel_username" type="text" :class="$inputClass" :value="old('cpanel_username', $hostedProject->cpanel_username)" placeholder="{{ __('e.g. pradymfi') }}" />
            </div>
            <div>
                <x-input-label for="status" :value="__('Status')" :required="true" />
                <select id="status" name="status" class="{{ $selectClass }}" required>
                    @foreach (['active', 'maintenance', 'suspended'] as $st)
                        <option value="{{ $st }}" @selected(old('status', $hostedProject->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="{{ $textareaClass }}" placeholder="{{ __('Internal notes about this hosted instance…') }}">{{ old('notes', $hostedProject->notes) }}</textarea>
            </div>
        </div>
    </x-admin.form-section>
</div>
