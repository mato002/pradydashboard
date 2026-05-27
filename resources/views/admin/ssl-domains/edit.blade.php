@php
    $inputClass = 'mt-1.5 block w-full min-h-[44px] rounded-xl border border-slate-200/80 bg-slate-50/80 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100 dark:placeholder:text-slate-500';
    $selectClass = $inputClass.' cursor-pointer';
@endphp

<x-dashboard-layout :heading="__('Edit domain')" :subheading="$domain->domain">
    <x-admin.form-shell
        :title="__('Configure routing')"
        :subtitle="__('Update tenant, server, and hosted project assignments for this domain.')"
        :badge="__('Networking')"
        :back-href="route('ssl-domains.index')"
        :back-label="__('Back to SSL & Domains')"
    >
        <form method="post" action="{{ route('ssl-domains.update', $domain) }}" class="max-w-3xl space-y-6">
            @csrf
            @method('PUT')

            <x-admin.form-section :title="__('Domain')" :description="__('Hostname cannot be changed here.')">
                <p class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $domain->domain }}</p>
            </x-admin.form-section>

            <x-admin.form-section :title="__('Assignments')" :description="__('Link tenant, server, or hosted project.')">
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <x-input-label for="tenant_id" :value="__('Tenant')" />
                        <select id="tenant_id" name="tenant_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected(old('tenant_id', $domain->tenant_id) == $tenant->id)>{{ $tenant->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="server_id" :value="__('Server')" />
                        <select id="server_id" name="server_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}" @selected(old('server_id', $domain->server_id) == $server->id)>{{ $server->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="hosted_project_id" :value="__('Hosted project')" />
                        <select id="hosted_project_id" name="hosted_project_id" class="{{ $selectClass }}">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($hostedProjects as $hp)
                                <option value="{{ $hp->id }}" @selected(old('hosted_project_id', $domain->hosted_project_id) == $hp->id)>{{ $hp->domain }} @if($hp->product) ({{ $hp->product->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="registrar" :value="__('Registrar')" />
                        <x-text-input id="registrar" name="registrar" type="text" :class="$inputClass" :value="old('registrar', $domain->registrar)" placeholder="{{ __('e.g. Cloudflare') }}" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-input-label for="routing_target" :value="__('Routing target')" />
                        <x-text-input id="routing_target" name="routing_target" type="text" :class="$inputClass.' font-mono'" :value="old('routing_target', $domain->routing_target)" placeholder="{{ __('e.g. edge-lb-01') }}" />
                    </div>
                    <div class="sm:col-span-3">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="auto_renew" value="0" />
                            <input type="checkbox" name="auto_renew" value="1" class="rounded border-slate-300 text-emerald-600" @checked(old('auto_renew', $domain->auto_renew)) />
                            {{ __('Auto-renew SSL') }}
                        </label>
                    </div>
                </div>
            </x-admin.form-section>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-card dark:border-slate-800 dark:bg-slate-900/60">
                <button type="submit" class="inline-flex min-h-[48px] items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110">
                    {{ __('Save routing') }}
                </button>
                <a href="{{ route('ssl-domains.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.form-shell>
</x-dashboard-layout>
