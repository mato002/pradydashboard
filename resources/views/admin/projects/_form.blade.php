<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="name" :value="__('Project name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="domain" :value="__('Domain / subdomain')" />
        <x-text-input id="domain" name="domain" type="text" class="mt-1 block w-full" :value="old('domain', $project->domain)" placeholder="property.pradytecai.com" required />
        <x-input-error class="mt-2" :messages="$errors->get('domain')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="product_slug" :value="__('Product slug (license API)')" />
        <x-text-input id="product_slug" name="product_slug" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('product_slug', $project->product_slug)" placeholder="property_management" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Lowercase letters, numbers, underscores. Sent as product in POST /api/license/check.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('product_slug')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="server_id" :value="__('Server')" />
        <select id="server_id" name="server_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <option value="">{{ __('Unassigned') }}</option>
            @foreach ($servers as $srv)
                <option value="{{ $srv->id }}" @selected(old('server_id', $project->server_id) == $srv->id)>{{ $srv->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('server_id')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="technology_stack" :value="__('Technology stack')" />
        <textarea id="technology_stack" name="technology_stack" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Laravel 11, MySQL, Redis…">{{ old('technology_stack', $project->technology_stack) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('technology_stack')" />
    </div>

    <div>
        <x-input-label for="git_repository" :value="__('Git repository')" />
        <x-text-input id="git_repository" name="git_repository" type="url" class="mt-1 block w-full" :value="old('git_repository', $project->git_repository)" />
        <x-input-error class="mt-2" :messages="$errors->get('git_repository')" />
    </div>

    <div>
        <x-input-label for="database_name" :value="__('Database name')" />
        <x-text-input id="database_name" name="database_name" type="text" class="mt-1 block w-full" :value="old('database_name', $project->database_name)" />
        <x-input-error class="mt-2" :messages="$errors->get('database_name')" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach (['active', 'maintenance', 'suspended'] as $st)
                <option value="{{ $st }}" @selected(old('status', $project->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div>
        <x-input-label for="version" :value="__('Version')" />
        <x-text-input id="version" name="version" type="text" class="mt-1 block w-full" :value="old('version', $project->version)" />
        <x-input-error class="mt-2" :messages="$errors->get('version')" />
    </div>

    <div>
        <x-input-label for="monthly_revenue" :value="__('Monthly revenue')" />
        <x-text-input id="monthly_revenue" name="monthly_revenue" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_revenue', $project->monthly_revenue)" />
        <x-input-error class="mt-2" :messages="$errors->get('monthly_revenue')" />
    </div>

    <div>
        <x-input-label for="monthly_cost" :value="__('Monthly cost')" />
        <x-text-input id="monthly_cost" name="monthly_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_cost', $project->monthly_cost)" />
        <x-input-error class="mt-2" :messages="$errors->get('monthly_cost')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('notes', $project->notes) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>
