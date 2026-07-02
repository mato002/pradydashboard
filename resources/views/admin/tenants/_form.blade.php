@php
    $section = $section ?? 'all';
    $compact = $compact ?? false;
    $selectClass = $selectClass ?? 'mt-1 block w-full rounded-xl border-slate-200/80 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    $textareaClass = $textareaClass ?? $selectClass.' min-h-[80px]';
    $plans = $plans ?? collect();
    $show = fn (string ...$names): bool => $section === 'all' || in_array($section, $names, true);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    @if ($show('organization'))
        <div class="md:col-span-2">
            <x-input-label for="company_name" :value="__('Company name')" />
            <x-text-input
                id="company_name"
                name="company_name"
                type="text"
                class="{{ $selectClass }}"
                :value="old('company_name', $tenant->company_name)"
                placeholder="{{ __('e.g. Matware SACCO') }}"
                required
                :autofocus="$section === 'organization' || $section === 'all'"
            />
            <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
        </div>

        @unless ($compact)
        <div>
            <x-input-label for="business_type" :value="__('Business type')" />
            <x-text-input id="business_type" name="business_type" type="text" class="{{ $selectClass }}" :value="old('business_type', $tenant->business_type)" placeholder="Property, Retail, Healthcare…" />
                    <x-input-error class="mt-2" :messages="$errors->get('business_type')" />
                </div>

                <div>
                    <x-input-label for="kra_pin" :value="__('KRA PIN')" />
                    <x-text-input id="kra_pin" name="kra_pin" type="text" class="{{ $selectClass }}" :value="old('kra_pin', $tenant->kra_pin)" />
                    <x-input-error class="mt-2" :messages="$errors->get('kra_pin')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="physical_address" :value="__('Physical address')" />
                    <textarea id="physical_address" name="physical_address" rows="2" class="{{ $textareaClass }}">{{ old('physical_address', $tenant->physical_address) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('physical_address')" />
                </div>
        @endunless

                <div>
                    <x-input-label for="country" :value="__('Country')" />
                    <select id="country" name="country" class="{{ $selectClass }}">
                        <option value="">{{ __('Select country…') }}</option>
                        @foreach (\App\Support\Phone\EastAfricaPhone::countries() as $eaCountry)
                            <option
                                value="{{ $eaCountry['iso'] }}"
                                @selected(strtoupper(old('country', $tenant->country ?? 'KE')) === $eaCountry['iso'])
                            >
                                {{ $eaCountry['name'] }} ({{ $eaCountry['iso'] }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                </div>

                @if ($tenant->exists)
                    <details
                        class="group md:col-span-2 rounded-xl border border-dashed border-slate-200/80 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-950/40"
                        @if (filled(old('logo_path', $tenant->logo_path))) open @endif
                    >
                        <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-700 marker:content-none dark:text-slate-200 [&::-webkit-details-marker]:hidden">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ __('Branding (optional)') }}
                            </span>
                            <span class="mt-0.5 block text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('Skip this — not required to provision or go live.') }}</span>
                        </summary>
                        <div class="space-y-2 border-t border-slate-200/80 px-4 py-4 dark:border-slate-700">
                            <x-input-label for="logo_path" :value="__('Company logo URL')" />
                            <x-text-input id="logo_path" name="logo_path" type="text" class="{{ $selectClass }}" :value="old('logo_path', $tenant->logo_path)" placeholder="{{ __('Optional — https://… or leave blank') }}" />
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Public image URL only. Leave blank if you do not have a logo yet.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('logo_path')" />
                        </div>
                    </details>
                @endif

                <div>
                    <x-input-label for="contact_person" :value="__('Contact person')" />
                    <x-text-input id="contact_person" name="contact_person" type="text" class="{{ $selectClass }}" :value="old('contact_person', $tenant->contact_person)" />
                    <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                </div>

                @php
                    $phoneParts = \App\Support\Phone\EastAfricaPhone::parse(
                        $tenant->phone,
                        \App\Support\Phone\EastAfricaPhone::dialForIso(old('country', $tenant->country ?? 'KE'))
                    );
                @endphp
                <div>
                    <x-input-label for="phone_local" :value="__('Phone')" />
                    <x-phone-input
                        :dial-code="$phoneParts['dial_code']"
                        :local="$phoneParts['local']"
                        :select-class="$selectClass"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="{{ $selectClass }}" :value="old('email', $tenant->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
            @endif

    @if ($show('product'))
        <div class="md:col-span-2">
            <x-input-label for="project_id" :value="__('Hosted product')" />
            <select id="project_id" name="project_id" class="{{ $selectClass }}" required>
                <option value="" disabled @selected(! old('project_id', $tenant->project_id ?? ($preselectedProjectId ?? null)))>{{ __('Select hosted product…') }}</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected(old('project_id', $tenant->project_id ?? ($preselectedProjectId ?? null)) == $p->id)>{{ $p->name }} — {{ $p->domain }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('project_id')" />
        </div>

        @unless ($compact)
        <div class="md:col-span-2">
            <x-input-label for="server_id" :value="__('Assigned server (optional)')" />
            <select id="server_id" name="server_id" class="{{ $selectClass }}">
                <option value="">{{ __('Same as project / not set') }}</option>
                @foreach ($servers as $srv)
                    <option value="{{ $srv->id }}" @selected(old('server_id', $tenant->server_id) == $srv->id)>{{ $srv->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('server_id')" />
        </div>

        <div>
            <x-input-label for="tenant_key" :value="__('Tenant key (for license API)')" />
            <x-text-input id="tenant_key" name="tenant_key" type="text" class="{{ $selectClass }} font-mono text-sm" :value="old('tenant_key', $tenant->tenant_key)" placeholder="{{ __('e.g. matware-sacco — auto-generated if empty') }}" />
            <p class="mt-1 text-xs text-slate-500">{{ __('Used as PRADY_TENANT_KEY in the hosted product .env') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('tenant_key')" />
        </div>

        <div>
            <x-input-label for="tenant_code" :value="__('Tenant code')" />
            <x-text-input id="tenant_code" name="tenant_code" type="text" class="{{ $selectClass }} font-mono text-sm uppercase" :value="old('tenant_code', $tenant->tenant_code)" placeholder="{{ __('e.g. MATWARE') }}" />
            <x-input-error class="mt-2" :messages="$errors->get('tenant_code')" />
        </div>
        @endunless

        <div class="{{ $compact ? 'md:col-span-2' : 'md:col-span-2' }}">
            <x-input-label for="tenant_domain" :value="__('Tenant application domain')" />
            <x-text-input id="tenant_domain" name="tenant_domain" type="text" class="{{ $selectClass }} font-mono text-sm" :value="old('tenant_domain', $tenant->tenant_domain)" placeholder="acme-mfi.pradytecai.com" :required="$compact" />
            <p class="mt-1 text-xs text-slate-500">{{ __('Must match the host users open in the browser (license check).') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('tenant_domain')" />
        </div>

        @unless ($compact)
        <div>
            <x-input-label for="login_url" :value="__('Login URL')" />
            <x-text-input id="login_url" name="login_url" type="url" class="mt-1 block w-full" :value="old('login_url', $tenant->login_url)" />
            <x-input-error class="mt-2" :messages="$errors->get('login_url')" />
        </div>
        @endunless
    @endif

    @if ($show('billing'))
        @if ($plans->isNotEmpty())
            <div class="{{ $compact ? 'md:col-span-2' : 'md:col-span-2' }}">
                <x-input-label for="saas_plan_id" :value="__('SaaS plan')" />
                <select id="saas_plan_id" name="saas_plan_id" class="{{ $selectClass }}">
                    <option value="">{{ $compact ? __('Select a plan…') : __('Custom / manual') }}</option>
                    @foreach ($plans as $plan)
                        <option
                            value="{{ $plan->id }}"
                            data-name="{{ $plan->name }}"
                            data-amount="{{ $plan->monthly_price }}"
                            @selected(old('saas_plan_id') == $plan->id)
                        >
                            {{ $plan->name }} — {{ $plan->formattedMonthly() }}/mo
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('saas_plan_id')" />
            </div>
        @endif

        @if ($compact)
            <input type="hidden" name="subscription_plan" id="subscription_plan" value="{{ old('subscription_plan', $tenant->subscription_plan) }}" />
            <input type="hidden" name="subscription_amount" id="subscription_amount" value="{{ old('subscription_amount', $tenant->subscription_amount ?? 0) }}" />
            <input type="hidden" name="tenant_currency" value="{{ old('tenant_currency', $tenant->tenant_currency ?? 'KES') }}" />
            <input type="hidden" name="billing_cycle" value="{{ old('billing_cycle', $tenant->billing_cycle ?? 'monthly') }}" />
            <input type="hidden" name="status" value="{{ old('status', $tenant->status ?? 'trial') }}" />
            <input type="hidden" name="start_date" value="{{ old('start_date', optional($tenant->start_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" />
            <input type="hidden" name="grace_days" value="{{ old('grace_days', $tenant->grace_days ?? 7) }}" />
            <input type="hidden" name="penalties_total" value="{{ old('penalties_total', $tenant->penalties_total ?? 0) }}" />
        @else
        <div>
            <x-input-label for="subscription_plan" :value="__('Plan label')" />
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
            <select id="billing_cycle" name="billing_cycle" class="{{ $selectClass }}" required>
                @foreach (['monthly', 'annual'] as $c)
                    <option value="{{ $c }}" @selected(old('billing_cycle', $tenant->billing_cycle ?? 'monthly') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('billing_cycle')" />
        </div>

        <div>
            <x-input-label for="start_date" :value="__('Start date')" />
            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($tenant->start_date)->format('Y-m-d') ?? now()->format('Y-m-d'))" />
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
                    <select id="status" name="status" class="{{ $selectClass }}" required>
                        @foreach (['trial', 'active', 'warning', 'restricted', 'suspended', 'overdue', 'cancelled', 'terminated'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $tenant->status ?? 'trial') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
        @endif
            @endif

    @if ($show('infrastructure') && ! $compact)
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
            <x-input-label for="deployment_version" :value="__('Deployment version')" />
            <x-text-input id="deployment_version" name="deployment_version" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('deployment_version', $tenant->deployment_version)" placeholder="v2.4.1" />
            <x-input-error class="mt-2" :messages="$errors->get('deployment_version')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="notes" :value="__('Internal notes')" />
            <textarea id="notes" name="notes" rows="3" class="{{ $textareaClass }}">{{ old('notes', $tenant->notes) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>
    @endif
</div>
