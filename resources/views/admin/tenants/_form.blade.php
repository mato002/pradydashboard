<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="company_name" :value="__('Company name')" />
        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $tenant->company_name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
    </div>

    <div>
        <x-input-label for="business_type" :value="__('Business type')" />
        <x-text-input id="business_type" name="business_type" type="text" class="mt-1 block w-full" :value="old('business_type', $tenant->business_type)" />
        <x-input-error class="mt-2" :messages="$errors->get('business_type')" />
    </div>

    <div>
        <x-input-label for="kra_pin" :value="__('KRA PIN')" />
        <x-text-input id="kra_pin" name="kra_pin" type="text" class="mt-1 block w-full" :value="old('kra_pin', $tenant->kra_pin)" />
        <x-input-error class="mt-2" :messages="$errors->get('kra_pin')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="physical_address" :value="__('Physical address')" />
        <textarea id="physical_address" name="physical_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('physical_address', $tenant->physical_address) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('physical_address')" />
    </div>

    <div>
        <x-input-label for="country" :value="__('Country (ISO-2)')" />
        <x-text-input id="country" name="country" type="text" maxlength="2" class="mt-1 block w-full uppercase" :value="old('country', $tenant->country)" placeholder="KE" />
        <x-input-error class="mt-2" :messages="$errors->get('country')" />
    </div>

    <div>
        <x-input-label for="logo_path" :value="__('Logo path / URL')" />
        <x-text-input id="logo_path" name="logo_path" type="text" class="mt-1 block w-full" :value="old('logo_path', $tenant->logo_path)" />
        <x-input-error class="mt-2" :messages="$errors->get('logo_path')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="project_id" :value="__('Hosted product')" />
        <select id="project_id" name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}" @selected(old('project_id', $tenant->project_id) == $p->id)>{{ $p->name }} — {{ $p->domain }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="server_id" :value="__('Assigned server (optional)')" />
        <select id="server_id" name="server_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            <option value="">{{ __('Same as project / not set') }}</option>
            @foreach ($servers as $srv)
                <option value="{{ $srv->id }}" @selected(old('server_id', $tenant->server_id) == $srv->id)>{{ $srv->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('server_id')" />
    </div>

    <div>
        <x-input-label for="contact_person" :value="__('Contact person')" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $tenant->contact_person)" />
        <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $tenant->phone)" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $tenant->email)" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="subscription_plan" :value="__('Subscription plan')" />
        <x-text-input id="subscription_plan" name="subscription_plan" type="text" class="mt-1 block w-full" :value="old('subscription_plan', $tenant->subscription_plan)" />
        <x-input-error class="mt-2" :messages="$errors->get('subscription_plan')" />
    </div>

    <div>
        <x-input-label for="subscription_amount" :value="__('Subscription amount')" />
        <x-text-input id="subscription_amount" name="subscription_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('subscription_amount', $tenant->subscription_amount)" />
        <x-input-error class="mt-2" :messages="$errors->get('subscription_amount')" />
    </div>

    <div>
        <x-input-label for="tenant_currency" :value="__('Currency')" />
        <x-text-input id="tenant_currency" name="tenant_currency" type="text" maxlength="3" class="mt-1 block w-full uppercase" :value="old('tenant_currency', $tenant->tenant_currency ?? 'KES')" required />
        <x-input-error class="mt-2" :messages="$errors->get('tenant_currency')" />
    </div>

    <div>
        <x-input-label for="billing_cycle" :value="__('Billing cycle')" />
        <select id="billing_cycle" name="billing_cycle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach (['monthly', 'annual'] as $c)
                <option value="{{ $c }}" @selected(old('billing_cycle', $tenant->billing_cycle ?? 'monthly') === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />
    </div>

    <div>
        <x-input-label for="start_date" :value="__('Start date')" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($tenant->start_date)->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
    </div>

    <div>
        <x-input-label for="renewal_date" :value="__('Renewal date')" />
        <x-text-input id="renewal_date" name="renewal_date" type="date" class="mt-1 block w-full" :value="old('renewal_date', optional($tenant->renewal_date)->format('Y-m-d'))" />
        <x-input-error class="mt-2" :messages="$errors->get('renewal_date')" />
    </div>

    <div>
        <x-input-label for="grace_days" :value="__('Grace period (days)')" />
        <x-text-input id="grace_days" name="grace_days" type="number" min="0" max="365" class="mt-1 block w-full" :value="old('grace_days', $tenant->grace_days ?? 7)" />
        <x-input-error class="mt-2" :messages="$errors->get('grace_days')" />
    </div>

    <div>
        <x-input-label for="penalties_total" :value="__('Penalties total')" />
        <x-text-input id="penalties_total" name="penalties_total" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('penalties_total', $tenant->penalties_total ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('penalties_total')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="status" :value="__('Lifecycle status')" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach (['active', 'trial', 'warning', 'restricted', 'suspended', 'overdue', 'cancelled', 'terminated'] as $st)
                <option value="{{ $st }}" @selected(old('status', $tenant->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>

    <div>
        <x-input-label for="cpanel_account_ref" :value="__('cPanel account ref')" />
        <x-text-input id="cpanel_account_ref" name="cpanel_account_ref" type="text" class="mt-1 block w-full" :value="old('cpanel_account_ref', $tenant->cpanel_account_ref)" />
        <x-input-error class="mt-2" :messages="$errors->get('cpanel_account_ref')" />
    </div>

    <div>
        <x-input-label for="database_ref" :value="__('Database ref')" />
        <x-text-input id="database_ref" name="database_ref" type="text" class="mt-1 block w-full" :value="old('database_ref', $tenant->database_ref)" />
        <x-input-error class="mt-2" :messages="$errors->get('database_ref')" />
    </div>

    <div>
        <x-input-label for="tenant_domain" :value="__('Tenant application domain')" />
        <x-text-input id="tenant_domain" name="tenant_domain" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('tenant_domain', $tenant->tenant_domain)" placeholder="abc.property.pradytecai.com" />
        <x-input-error class="mt-2" :messages="$errors->get('tenant_domain')" />
    </div>

    <div>
        <x-input-label for="deployment_version" :value="__('Deployment version')" />
        <x-text-input id="deployment_version" name="deployment_version" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('deployment_version', $tenant->deployment_version)" />
        <x-input-error class="mt-2" :messages="$errors->get('deployment_version')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="login_url" :value="__('Login URL')" />
        <x-text-input id="login_url" name="login_url" type="url" class="mt-1 block w-full" :value="old('login_url', $tenant->login_url)" />
        <x-input-error class="mt-2" :messages="$errors->get('login_url')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Internal notes')" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('notes', $tenant->notes) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>
