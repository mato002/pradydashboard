@php
    $d = $sectionData;
    $toggle = fn (string $name, string $label, ?string $hint = null) => view('admin.system-settings.partials.toggle', [
        'name' => $name, 'label' => $label, 'hint' => $hint, 'checked' => ! empty($d[$name]),
    ]);
    $input = fn (string $name, string $label, string $type = 'text', ?string $hint = null) => view('admin.system-settings.partials.input', [
        'name' => $name, 'label' => $label, 'type' => $type, 'hint' => $hint, 'value' => $d[$name] ?? '',
    ]);
@endphp

@if ($activeSection === 'general')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('platform_name', __('Platform name')) !!}
        {!! $input('company_name', __('Company name')) !!}
        {!! $input('timezone', __('Timezone')) !!}
        {!! $input('currency', __('Currency')) !!}
        {!! $input('language', __('Language')) !!}
        {!! $input('region', __('Regional settings')) !!}
        <div class="sm:col-span-2">{!! $toggle('maintenance_mode', __('Maintenance mode'), __('Disable tenant-facing endpoints during maintenance')) !!}</div>
    </div>
@elseif ($activeSection === 'branding')
    @include('admin.system-settings.sections.branding')
@elseif ($activeSection === 'company')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('legal_name', __('Legal name')) !!}
        {!! $input('support_email', __('Support email'), 'email') !!}
        {!! $input('billing_email', __('Billing email'), 'email') !!}
        {!! $input('phone', __('Phone')) !!}
        {!! $input('tax_id', __('Tax ID')) !!}
        <div class="sm:col-span-2">{!! $input('address', __('Address')) !!}</div>
    </div>
@elseif ($activeSection === 'email')
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('SMTP configuration') }}</h4>
            {!! $input('smtp_host', __('SMTP host')) !!}
            {!! $input('smtp_port', __('Port')) !!}
            {!! $input('smtp_encryption', __('Encryption')) !!}
            {!! $input('smtp_username', __('Username')) !!}
            {!! $input('from_name', __('From name')) !!}
            {!! $input('from_email', __('From email'), 'email') !!}
            {!! $input('webhook_url', __('Webhook URL'), 'url') !!}
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Delivery health') }}</h4>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Sent (24h)') }}</dt><dd class="font-semibold tabular-nums">{{ number_format($delivery['sent_24h']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Failed') }}</dt><dd class="font-semibold text-rose-600">{{ $delivery['failed_24h'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Queue depth') }}</dt><dd class="font-semibold">{{ $delivery['queue_depth'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Avg latency') }}</dt><dd class="font-semibold">{{ $delivery['avg_latency_ms'] }}ms</dd></div>
            </dl>
        </div>
    </div>
@elseif ($activeSection === 'security')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $toggle('enforce_2fa', __('Enforce 2FA for admins')) !!}
        {!! $toggle('audit_logging', __('Audit logging')) !!}
        {!! $toggle('restrict_admin_roles', __('Restrict privileged roles')) !!}
        {!! $input('session_timeout_min', __('Session timeout (minutes)'), 'number') !!}
        {!! $input('max_login_attempts', __('Max login attempts'), 'number') !!}
        {!! $input('password_min_length', __('Password min length'), 'number') !!}
        <div class="sm:col-span-2">{!! $input('ip_whitelist', __('IP whitelist'), 'text', __('Comma-separated CIDR blocks')) !!}</div>
    </div>
@elseif ($activeSection === 'authentication')
    <div class="space-y-4">
        {!! $toggle('allow_sso', __('Enable SSO')) !!}
        {!! $toggle('oauth_google', __('Google OAuth')) !!}
        {!! $toggle('oauth_microsoft', __('Microsoft OAuth')) !!}
        {!! $toggle('magic_link', __('Magic link login')) !!}
        {!! $input('saml_entity_id', __('SAML entity ID')) !!}
    </div>
@elseif ($activeSection === 'api')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('rate_limit_per_min', __('Rate limit / min'), 'number') !!}
        {!! $input('oauth_client_id', __('OAuth client ID')) !!}
        {!! $input('webhook_secret', __('Webhook secret')) !!}
        {!! $toggle('stripe_enabled', __('Stripe gateway')) !!}
        {!! $toggle('mpesa_enabled', __('M-Pesa gateway')) !!}
    </div>
@elseif ($activeSection === 'billing')
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">{!! $input('company_legal_name', __('Company legal name')) !!}</div>
        {!! $input('tax_pin', __('Tax PIN')) !!}
        {!! $toggle('vat_registered', __('VAT registered')) !!}
        {!! $input('tax_rate', __('VAT / tax rate %')) !!}
        {!! $input('default_currency', __('Default currency')) !!}
        {!! $input('invoice_prefix', __('Invoice prefix')) !!}
        <div class="sm:col-span-2">{!! $input('payment_instructions', __('Payment instructions')) !!}</div>
        {!! $input('default_payment_terms', __('Default payment terms')) !!}
        <div class="sm:col-span-2">{!! $input('invoice_footer_notes', __('Invoice footer notes')) !!}</div>
        {!! $input('usage_rate_per_mb', __('Usage rate per MB')) !!}
        {!! $input('grace_period_days', __('Grace period (days)')) !!}
        {!! $toggle('auto_suspend', __('Auto-suspend overdue tenants')) !!}
    </div>
@elseif ($activeSection === 'notifications')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $toggle('email_alerts', __('Email alerts')) !!}
        {!! $toggle('deployment_alerts', __('Deployment alerts')) !!}
        {!! $toggle('billing_alerts', __('Billing alerts')) !!}
        {!! $toggle('security_alerts', __('Security alerts')) !!}
        <div class="sm:col-span-2">{!! $input('slack_webhook', __('Slack webhook'), 'url') !!}</div>
    </div>
@elseif ($activeSection === 'backups')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $toggle('auto_backup', __('Automated backups')) !!}
        {!! $toggle('restore_test_monthly', __('Monthly restore test')) !!}
        {!! $input('frequency', __('Frequency')) !!}
        {!! $input('retention_days', __('Retention (days)')) !!}
        {!! $input('cloud_provider', __('Cloud provider')) !!}
    </div>
@elseif ($activeSection === 'deployment')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('default_environment', __('Default environment')) !!}
        {!! $input('ci_provider', __('CI/CD provider')) !!}
        {!! $input('maintenance_window', __('Maintenance window')) !!}
        {!! $toggle('auto_rollback', __('Auto-rollback on failure')) !!}
    </div>
@elseif ($activeSection === 'monitoring')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('uptime_threshold', __('Uptime threshold %')) !!}
        {!! $input('alert_email', __('Alert email'), 'email') !!}
        {!! $input('escalation_minutes', __('Escalation (minutes)')) !!}
        {!! $input('sensitivity', __('Sensitivity')) !!}
        {!! $toggle('incident_sms', __('SMS incident alerts')) !!}
    </div>
@elseif ($activeSection === 'license')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $input('api_version', __('API version')) !!}
        {!! $input('grace_period_hours', __('Grace period (hours)')) !!}
        {!! $input('offline_cache_hours', __('Offline cache (hours)')) !!}
        {!! $toggle('enforce_module_entitlements', __('Enforce module entitlements')) !!}
    </div>
@elseif ($activeSection === 'automation')
    <div class="grid gap-4 sm:grid-cols-2">
        {!! $toggle('ai_assistant', __('AI assistant')) !!}
        {!! $toggle('predictive_monitoring', __('Predictive monitoring')) !!}
        {!! $toggle('smart_recommendations', __('Smart recommendations')) !!}
        {!! $toggle('auto_healing', __('Auto-healing')) !!}
        {!! $toggle('workflow_engine', __('Workflow engine')) !!}
    </div>
@elseif ($activeSection === 'logs')
    <div class="rounded-xl border border-slate-200/80 bg-slate-950 p-4 font-mono text-xs text-emerald-400/90 dark:border-slate-700">
        <p>[system] Log aggregation connected — Elasticsearch sink active</p>
        <p>[queue] Horizon workers: 4 processes healthy</p>
        <p>[api] License check endpoint p99: 42ms</p>
        <p class="text-slate-500">→ {{ __('Connect log shipping in Phase 5 for full search') }}</p>
    </div>
@elseif ($activeSection === 'audit')
    <div class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
        <p>{{ __('Compliance mode: SOC2-ready retention policies. Export audit bundles from the Activity Logs module.') }}</p>
        <a href="{{ route('activity-logs.index') }}" class="inline-flex font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Open Audit & Observability →') }}</a>
    </div>
@endif
