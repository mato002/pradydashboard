@php
    $inputClass = 'mt-1.5 block w-full min-h-[44px] rounded-xl border border-slate-200/80 bg-slate-50/80 px-3.5 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100';
    $selectClass = $inputClass.' cursor-pointer';
@endphp

<x-dashboard-layout :heading="__('Add domain')" :subheading="__('Register a managed zone and certificate')">
    <x-admin.form-shell
        :title="__('Register domain')"
        :subtitle="__('Add a hostname to SSL & DNS management. Live SSL probe runs on save when enabled.')"
        :badge="__('Networking')"
        :back-href="route('ssl-domains.index')"
        :back-label="__('Back to SSL & Domains')"
    >
        <form method="post" action="{{ route('ssl-domains.store') }}" class="max-w-3xl space-y-6">
            @csrf

            <x-admin.form-section :title="__('Domain identity')" :description="__('Primary hostname and routing.')">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="domain" :value="__('Domain name')" />
                        <x-text-input id="domain" name="domain" type="text" :class="$inputClass.' font-mono'" :value="old('domain')" placeholder="app.example.com or *.example.com" required autofocus />
                        <p class="mt-1.5 text-[11px] text-slate-500">{{ __('FQDN or wildcard — must be unique in the registry.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('domain')" />
                    </div>
                    <div>
                        <x-input-label for="registrar" :value="__('Registrar')" />
                        <x-text-input id="registrar" name="registrar" type="text" :class="$inputClass" :value="old('registrar')" placeholder="Cloudflare, Namecheap…" />
                        <x-input-error class="mt-2" :messages="$errors->get('registrar')" />
                    </div>
                    <div>
                        <x-input-label for="routing_target" :value="__('Routing target')" />
                        <x-text-input id="routing_target" name="routing_target" type="text" :class="$inputClass.' font-mono'" :value="old('routing_target')" placeholder="edge-lb-01" />
                        <x-input-error class="mt-2" :messages="$errors->get('routing_target')" />
                    </div>
                </div>
            </x-admin.form-section>

            <x-admin.form-section :title="__('Assignments')" :description="__('Link tenant, server, or product.')">
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <x-input-label for="tenant_id" :value="__('Tenant')" />
                        <select id="tenant_id" name="tenant_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>{{ $tenant->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="server_id" :value="__('Server')" />
                        <select id="server_id" name="server_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="project_id" :value="__('Project')" />
                        <select id="project_id" name="project_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-admin.form-section>

            <x-admin.form-section :title="__('Certificate & expiry')" :description="__('Optional overrides — leave blank to probe live SSL on save.')">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="ssl_issuer" :value="__('SSL issuer')" />
                        <x-text-input id="ssl_issuer" name="ssl_issuer" type="text" :class="$inputClass" :value="old('ssl_issuer')" placeholder="Let's Encrypt" />
                    </div>
                    <div>
                        <x-input-label for="ssl_expires_at" :value="__('SSL expiry')" />
                        <x-text-input id="ssl_expires_at" name="ssl_expires_at" type="date" :class="$inputClass" :value="old('ssl_expires_at')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="domain_expires_at" :value="__('Domain registration expiry')" />
                        <x-text-input id="domain_expires_at" name="domain_expires_at" type="date" :class="$inputClass" :value="old('domain_expires_at')" />
                    </div>
                </div>
                <label class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3">
                    <input type="hidden" name="probe_ssl" value="0" />
                    <input type="checkbox" name="probe_ssl" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('probe_ssl', true)) />
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ __('Probe live SSL certificate on save (port 443)') }}</span>
                </label>
            </x-admin.form-section>

            <x-admin.form-section :title="__('Flags')" :description="__('Classification for dashboards and alerts.')">
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="auto_renew" value="0" />
                        <input type="checkbox" name="auto_renew" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('auto_renew', true)) />
                        {{ __('Auto-renew') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_wildcard" value="0" />
                        <input type="checkbox" name="is_wildcard" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('is_wildcard')) />
                        {{ __('Wildcard cert') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_subdomain" value="0" />
                        <input type="checkbox" name="is_subdomain" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('is_subdomain')) />
                        {{ __('Subdomain') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_tenant_custom" value="0" />
                        <input type="checkbox" name="is_tenant_custom" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('is_tenant_custom')) />
                        {{ __('Tenant custom hostname') }}
                    </label>
                </div>
            </x-admin.form-section>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110">
                    {{ __('Register domain') }}
                </button>
                <a href="{{ route('ssl-domains.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>

