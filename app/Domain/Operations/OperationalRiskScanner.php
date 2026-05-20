<?php

namespace App\Domain\Operations;

use App\Models\OperationalRiskAcknowledgement;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\ServerProviderNotice;
use App\Models\StaffDocument;
use App\Models\StaffProfile;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantCommunication;
use App\Models\TenantInvoice;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Support\OperationalRiskCategory;
use App\Support\SupportOpsOptions;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OperationalRiskScanner
{
    public const RENEWAL_WARNING_DAYS = 30;

    public const SSL_WARNING_DAYS = 30;

    public const SYNC_STALE_HOURS = 48;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function scan(array $filters = []): Collection
    {
        $risks = collect()
            ->merge($this->scanOverdueInvoices())
            ->merge($this->scanTenantRenewals())
            ->merge($this->scanSubscriptionRenewals())
            ->merge($this->scanSslExpiring())
            ->merge($this->scanDocumentsExpiring())
            ->merge($this->scanStaffContractsExpiring())
            ->merge($this->scanOverdueFollowUps())
            ->merge($this->scanOverdueTickets())
            ->merge($this->scanServerRenewals())
            ->merge($this->scanFailedIntegrations())
            ->merge($this->scanOutdatedDeployments())
            ->merge($this->scanMissingContracts())
            ->merge($this->scanTelemetryIssues())
            ->merge($this->scanCriticalProviderNotices());

        $acknowledged = OperationalRiskAcknowledgement::query()->pluck('risk_key')->all();
        $risks = $risks->map(function (array $risk) use ($acknowledged): array {
            $risk['acknowledged'] = in_array($risk['key'], $acknowledged, true);

            return $risk;
        });

        return $this->applyFilters($risks, $filters)->sortBy([
            fn (array $r) => match ($r['severity']) {
                'critical' => 0,
                'warning' => 1,
                default => 2,
            },
            fn (array $r) => $r['due_at']?->timestamp ?? PHP_INT_MAX,
        ])->values();
    }

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>|null  $risks
     */
    public function grouped(array $filters = [], ?\Illuminate\Support\Collection $risks = null): array
    {
        $grouped = [];
        foreach (($risks ?? $this->scan($filters)) as $risk) {
            $grouped[$risk['category']][] = $risk;
        }

        $out = [];
        foreach (OperationalRiskCategory::all() as $category) {
            if (! empty($grouped[$category])) {
                $out[$category] = collect($grouped[$category]);
            }
        }

        return $out;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forTenant(int $tenantId, int $limit = 20): Collection
    {
        return $this->scan(['tenant_id' => $tenantId])->take($limit);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forProject(int $projectId, int $limit = 20): Collection
    {
        return $this->scan(['project_id' => $projectId])->take($limit);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forServer(int $serverId, int $limit = 20): Collection
    {
        return $this->scan(['server_id' => $serverId])->take($limit);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forStaff(int $staffProfileId, int $limit = 20): Collection
    {
        return $this->scan(['staff_profile_id' => $staffProfileId])->take($limit);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function attentionRequired(int $limit = 10): Collection
    {
        return $this->scan()
            ->reject(fn (array $r) => $r['acknowledged'])
            ->whereIn('severity', ['critical', 'warning'])
            ->take($limit)
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $risks
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $risks, array $filters): Collection
    {
        if ($category = $filters['category'] ?? null) {
            $risks = $risks->where('category', $category);
        }

        if ($severity = $filters['severity'] ?? null) {
            $risks = $risks->where('severity', $severity);
        }

        if ($tenantId = $filters['tenant_id'] ?? null) {
            $risks = $risks->where('tenant_id', (int) $tenantId);
        }

        if ($projectId = $filters['project_id'] ?? null) {
            $risks = $risks->where('project_id', (int) $projectId);
        }

        if ($serverId = $filters['server_id'] ?? null) {
            $risks = $risks->where('server_id', (int) $serverId);
        }

        if ($staffId = $filters['staff_profile_id'] ?? null) {
            $risks = $risks->where('staff_profile_id', (int) $staffId);
        }

        if ($from = $filters['from'] ?? null) {
            $risks = $risks->filter(fn (array $r) => $r['due_at'] && $r['due_at']->toDateString() >= $from);
        }

        if ($to = $filters['to'] ?? null) {
            $risks = $risks->filter(fn (array $r) => $r['due_at'] && $r['due_at']->toDateString() <= $to);
        }

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $needle = strtolower($search);
            $risks = $risks->filter(function (array $r) use ($needle): bool {
                return str_contains(strtolower($r['title'].' '.$r['description']), $needle);
            });
        }

        if (($filters['acknowledged'] ?? '') === 'yes') {
            $risks = $risks->where('acknowledged', true);
        } elseif (($filters['acknowledged'] ?? '') === 'no') {
            $risks = $risks->where('acknowledged', false);
        }

        return $risks->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanOverdueInvoices(): Collection
    {
        return TenantInvoice::query()
            ->with('tenant:id,company_name,project_id')
            ->whereIn('status', ['overdue', 'pending', 'partial', 'partially_paid', 'sent'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->get()
            ->map(fn (TenantInvoice $inv) => $this->risk(
                key: 'invoice_overdue:'.$inv->id,
                category: OperationalRiskCategory::BILLING,
                severity: 'critical',
                title: __('Overdue invoice :number', ['number' => $inv->invoice_number]),
                description: __(':tenant owes :amount — due :date', [
                    'tenant' => $inv->tenant?->company_name ?? __('Unknown'),
                    'amount' => $inv->formattedBalance(),
                    'date' => $inv->due_date?->toFormattedDateString() ?? '—',
                ]),
                action: __('Review invoice and send reminder or record payment'),
                dueAt: $inv->due_date,
                tenantId: $inv->tenant_id,
                projectId: $inv->tenant?->project_id,
                subject: $inv,
                url: route('invoices.show', $inv),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanTenantRenewals(): Collection
    {
        $threshold = now()->addDays(self::RENEWAL_WARNING_DAYS);

        return Tenant::query()
            ->whereNotNull('renewal_date')
            ->whereDate('renewal_date', '<=', $threshold)
            ->whereIn('status', ['active', 'trial', 'warning'])
            ->get()
            ->map(function (Tenant $tenant) {
                $past = $tenant->renewal_date->isPast();

                return $this->risk(
                    key: 'tenant_renewal:'.$tenant->id,
                    category: OperationalRiskCategory::RENEWAL,
                    severity: $past ? 'critical' : 'warning',
                    title: $past
                        ? __('Tenant renewal overdue')
                        : __('Tenant renewal due soon'),
                    description: __(':name renews on :date', [
                        'name' => $tenant->company_name,
                        'date' => $tenant->renewal_date->toFormattedDateString(),
                    ]),
                    action: __('Confirm renewal billing and contract status'),
                    dueAt: $tenant->renewal_date,
                    tenantId: $tenant->id,
                    projectId: $tenant->project_id,
                    subject: $tenant,
                    url: route('tenants.show', ['tenant' => $tenant, 'tab' => 'billing']),
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanSubscriptionRenewals(): Collection
    {
        $threshold = now()->addDays(self::RENEWAL_WARNING_DAYS);

        return TenantProjectSubscription::query()
            ->with(['tenant:id,company_name', 'project:id,name'])
            ->where(function ($q) use ($threshold): void {
                $q->where(function ($inner) use ($threshold): void {
                    $inner->whereNotNull('renewal_date')
                        ->whereDate('renewal_date', '<=', $threshold);
                })->orWhere(function ($inner) use ($threshold): void {
                    $inner->whereNotNull('trial_expires_at')
                        ->whereDate('trial_expires_at', '<=', $threshold);
                });
            })
            ->get()
            ->map(function (TenantProjectSubscription $sub) {
                $due = $sub->trial_expires_at && $sub->trial_expires_at->lte($sub->renewal_date ?? $sub->trial_expires_at)
                    ? $sub->trial_expires_at
                    : $sub->renewal_date;
                $past = $due?->isPast() ?? false;

                return $this->risk(
                    key: 'subscription_renewal:'.$sub->id,
                    category: OperationalRiskCategory::RENEWAL,
                    severity: $past ? 'critical' : 'warning',
                    title: $sub->trial_expires_at && $due?->eq($sub->trial_expires_at)
                        ? __('Trial expiring')
                        : __('Subscription renewal due'),
                    description: __(':tenant · :project — :date', [
                        'tenant' => $sub->tenant?->company_name ?? '—',
                        'project' => $sub->project?->name ?? '—',
                        'date' => $due?->toFormattedDateString() ?? '—',
                    ]),
                    action: __('Renew or convert subscription before lapse'),
                    dueAt: $due,
                    tenantId: $sub->tenant_id,
                    projectId: $sub->project_id,
                    subject: $sub,
                    url: route('tenants.show', ['tenant' => $sub->tenant_id, 'tab' => 'projects']),
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanSslExpiring(): Collection
    {
        $risks = collect();
        $threshold = now()->addDays(self::SSL_WARNING_DAYS);

        foreach (Server::query()->whereNotNull('ssl_days_remaining')->get() as $server) {
            if ($server->ssl_days_remaining !== null && $server->ssl_days_remaining <= self::SSL_WARNING_DAYS) {
                $risks->push($this->risk(
                    key: 'server_ssl:'.$server->id,
                    category: OperationalRiskCategory::SSL,
                    severity: $server->ssl_days_remaining <= 7 ? 'critical' : 'warning',
                    title: __('Server SSL expiring'),
                    description: __(':server — :days days remaining', [
                        'server' => $server->name,
                        'days' => $server->ssl_days_remaining,
                    ]),
                    action: __('Renew certificate or update SSL status'),
                    dueAt: now()->addDays(max(0, (int) $server->ssl_days_remaining)),
                    serverId: $server->id,
                    subject: $server,
                    url: route('servers.show', $server),
                ));
            }
        }

        TenantProjectInfrastructure::query()
            ->with(['subscription.tenant', 'subscription.project', 'server'])
            ->whereNotNull('ssl_expiry_date')
            ->whereDate('ssl_expiry_date', '<=', $threshold)
            ->each(function (TenantProjectInfrastructure $infra) use (&$risks, $threshold): void {
                $past = $infra->ssl_expiry_date->isPast();
                $risks->push($this->risk(
                    key: 'tenant_ssl:'.$infra->id,
                    category: OperationalRiskCategory::SSL,
                    severity: $past ? 'critical' : 'warning',
                    title: __('Tenant SSL expiring'),
                    description: __(':tenant · :domain — :date', [
                        'tenant' => $infra->subscription?->tenant?->company_name ?? '—',
                        'domain' => $infra->domain ?? $infra->subdomain ?? '—',
                        'date' => $infra->ssl_expiry_date->toFormattedDateString(),
                    ]),
                    action: __('Renew SSL for tenant deployment'),
                    dueAt: $infra->ssl_expiry_date,
                    tenantId: $infra->subscription?->tenant_id,
                    projectId: $infra->subscription?->project_id,
                    serverId: $infra->server_id,
                    subject: $infra,
                    url: route('tenants.show', [
                        'tenant' => $infra->subscription?->tenant_id,
                        'tab' => 'infrastructure',
                        'subscription' => $infra->tenant_project_subscription_id,
                    ]),
                ));
            });

        return $risks;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanDocumentsExpiring(): Collection
    {
        $threshold = now()->addDays(self::RENEWAL_WARNING_DAYS);

        return OperationalDocument::query()
            ->with('tenant:id,company_name,project_id')
            ->whereNotNull('expiry_date')
            ->where('status', '!=', 'archived')
            ->whereDate('expiry_date', '<=', $threshold)
            ->get()
            ->map(function (OperationalDocument $doc) {
                $past = $doc->expiry_date->isPast();

                return $this->risk(
                    key: 'document_expiring:'.$doc->id,
                    category: OperationalRiskCategory::DOCUMENT,
                    severity: $past ? 'critical' : 'warning',
                    title: $past ? __('Document expired') : __('Document expiring'),
                    description: __(':title for :tenant — :date', [
                        'title' => $doc->title,
                        'tenant' => $doc->tenant?->company_name ?? '—',
                        'date' => $doc->expiry_date->toFormattedDateString(),
                    ]),
                    action: __('Renew or archive the document'),
                    dueAt: $doc->expiry_date,
                    tenantId: $doc->tenant_id,
                    projectId: $doc->project_id,
                    subject: $doc,
                    url: route('tenants.show', ['tenant' => $doc->tenant_id, 'tab' => 'documents']),
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanStaffContractsExpiring(): Collection
    {
        $threshold = now()->addDays(self::RENEWAL_WARNING_DAYS);

        return StaffDocument::query()
            ->with('staffProfile:id,full_name')
            ->where('document_type', 'contract')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $threshold)
            ->get()
            ->map(function (StaffDocument $doc) {
                $past = $doc->expiry_date->isPast();

                return $this->risk(
                    key: 'staff_contract:'.$doc->id,
                    category: OperationalRiskCategory::HR,
                    severity: $past ? 'critical' : 'warning',
                    title: $past ? __('Staff contract expired') : __('Staff contract expiring'),
                    description: __(':staff — :title expires :date', [
                        'staff' => $doc->staffProfile?->full_name ?? '—',
                        'title' => $doc->title,
                        'date' => $doc->expiry_date->toFormattedDateString(),
                    ]),
                    action: __('Renew employment contract'),
                    dueAt: $doc->expiry_date,
                    staffProfileId: $doc->staff_profile_id,
                    subject: $doc,
                    url: route('hr.staff.show', ['staff' => $doc->staff_profile_id, 'tab' => 'documents']),
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanOverdueFollowUps(): Collection
    {
        return TenantCommunication::query()
            ->with('tenant:id,company_name,project_id')
            ->where('follow_up_required', true)
            ->where('status', 'pending_follow_up')
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<', now()->toDateString())
            ->get()
            ->map(fn (TenantCommunication $c) => $this->risk(
                key: 'follow_up_overdue:'.$c->id,
                category: OperationalRiskCategory::SUPPORT,
                severity: 'warning',
                title: __('Overdue follow-up'),
                description: __(':tenant — :channel communication', [
                    'tenant' => $c->tenant?->company_name ?? '—',
                    'channel' => $c->channel,
                ]),
                action: __('Complete follow-up or reschedule'),
                dueAt: $c->follow_up_date,
                tenantId: $c->tenant_id,
                projectId: $c->tenant?->project_id,
                subject: $c,
                url: route('tenants.show', ['tenant' => $c->tenant_id, 'tab' => 'communications']),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanOverdueTickets(): Collection
    {
        $open = SupportOpsOptions::openTicketStatuses();

        return SupportTicket::query()
            ->with(['tenant:id,company_name,project_id', 'assignedStaff:id,full_name'])
            ->whereIn('status', $open)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get()
            ->map(fn (SupportTicket $t) => $this->risk(
                key: 'ticket_overdue:'.$t->id,
                category: OperationalRiskCategory::SUPPORT,
                severity: in_array($t->priority, ['urgent', 'high'], true) ? 'critical' : 'warning',
                title: __('Overdue support ticket'),
                description: __(':subject — :tenant', [
                    'subject' => $t->subject,
                    'tenant' => $t->tenant?->company_name ?? __('Unassigned'),
                ]),
                action: __('Update ticket status or reassign'),
                dueAt: $t->due_at,
                tenantId: $t->tenant_id,
                projectId: $t->project_id,
                staffProfileId: $t->assigned_staff_id,
                subject: $t,
                url: route('support-tickets.show', $t->id),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanServerRenewals(): Collection
    {
        $threshold = now()->addDays(self::RENEWAL_WARNING_DAYS);

        return Server::query()
            ->whereNotNull('renewal_expires_at')
            ->whereDate('renewal_expires_at', '<=', $threshold)
            ->get()
            ->map(function (Server $server) {
                $past = $server->renewal_expires_at->isPast();

                return $this->risk(
                    key: 'server_renewal:'.$server->id,
                    category: OperationalRiskCategory::SERVER,
                    severity: $past ? 'critical' : 'warning',
                    title: $past ? __('Server renewal overdue') : __('Server renewal due'),
                    description: __(':name provider renewal on :date', [
                        'name' => $server->name,
                        'date' => $server->renewal_expires_at->toFormattedDateString(),
                    ]),
                    action: __('Renew hosting or provider contract'),
                    dueAt: $server->renewal_expires_at,
                    serverId: $server->id,
                    subject: $server,
                    url: route('servers.show', $server),
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanFailedIntegrations(): Collection
    {
        return TenantProjectServiceIntegration::query()
            ->with(['subscription.tenant', 'subscription.project'])
            ->where(function ($q): void {
                $q->where('status', 'error')
                    ->orWhere('last_test_status', 'fail');
            })
            ->get()
            ->map(fn (TenantProjectServiceIntegration $i) => $this->risk(
                key: 'integration_failed:'.$i->id,
                category: OperationalRiskCategory::INTEGRATION,
                severity: 'critical',
                title: __('Integration failure'),
                description: __(':name for :tenant — :error', [
                    'name' => $i->display_name,
                    'tenant' => $i->subscription?->tenant?->company_name ?? '—',
                    'error' => $i->last_error ? \Illuminate\Support\Str::limit($i->last_error, 80) : __('Test failed'),
                ]),
                action: __('Review credentials and re-test connection'),
                dueAt: $i->last_tested_at ?? now(),
                tenantId: $i->subscription?->tenant_id,
                projectId: $i->subscription?->project_id,
                subject: $i,
                url: route('tenants.show', [
                    'tenant' => $i->subscription?->tenant_id,
                    'tab' => 'integrations',
                    'subscription' => $i->tenant_project_subscription_id,
                ]),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanOutdatedDeployments(): Collection
    {
        return TenantProjectVersion::query()
            ->with(['subscription.tenant', 'subscription.project'])
            ->whereIn('update_status', ['outdated', 'critical_update_required'])
            ->get()
            ->map(fn (TenantProjectVersion $v) => $this->risk(
                key: 'deployment_outdated:'.$v->id,
                category: OperationalRiskCategory::DEPLOYMENT,
                severity: $v->update_status === 'critical_update_required' ? 'critical' : 'warning',
                title: __('Outdated deployment'),
                description: __(':tenant on :current (latest :latest)', [
                    'tenant' => $v->subscription?->tenant?->company_name ?? '—',
                    'current' => $v->current_version ?? '—',
                    'latest' => $v->latest_version ?? '—',
                ]),
                action: __('Schedule version rollout'),
                dueAt: $v->last_checked_at ?? now(),
                tenantId: $v->subscription?->tenant_id,
                projectId: $v->subscription?->project_id,
                subject: $v,
                url: route('tenants.show', [
                    'tenant' => $v->subscription?->tenant_id,
                    'tab' => 'versions',
                    'subscription' => $v->tenant_project_subscription_id,
                ]),
            ));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanMissingContracts(): Collection
    {
        $risks = collect();

        $tenants = Tenant::query()
            ->with(['projectSubscriptions.project', 'operationalDocuments'])
            ->whereHas('projectSubscriptions')
            ->get();

        foreach ($tenants as $tenant) {
            $signed = $tenant->operationalDocuments
                ->where('status', 'signed')
                ->whereIn('document_type', \App\Support\OperationalDocumentOptions::contractTypes());

            foreach ($tenant->projectSubscriptions as $sub) {
                if (! $sub->project?->contract_document_required) {
                    continue;
                }

                $has = $signed->contains(function (OperationalDocument $doc) use ($sub): bool {
                    return $doc->tenant_project_subscription_id === $sub->id
                        || ($doc->project_id === $sub->project_id && ! $doc->tenant_project_subscription_id);
                });

                if (! $has) {
                    $risks->push($this->risk(
                        key: 'missing_contract:'.$sub->id,
                        category: OperationalRiskCategory::CONTRACT,
                        severity: 'warning',
                        title: __('Missing signed contract'),
                        description: __(':tenant — :project requires a contract on file', [
                            'tenant' => $tenant->company_name,
                            'project' => $sub->project?->name ?? '—',
                        ]),
                        action: __('Upload signed contract in Documents tab'),
                        dueAt: now(),
                        tenantId: $tenant->id,
                        projectId: $sub->project_id,
                        subject: $sub,
                        url: route('tenants.show', ['tenant' => $tenant->id, 'tab' => 'documents']),
                    ));
                }
            }
        }

        return $risks;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanTelemetryIssues(): Collection
    {
        $risks = collect();
        $staleBefore = now()->subHours(self::SYNC_STALE_HOURS);

        Server::query()->each(function (Server $server) use (&$risks, $staleBefore): void {
            if ($server->telemetry_mode === 'manual') {
                $risks->push($this->risk(
                    key: 'telemetry_manual:'.$server->id,
                    category: OperationalRiskCategory::INFRASTRUCTURE,
                    severity: 'info',
                    title: __('Manual telemetry only'),
                    description: __(':server is not on automatic sync', ['server' => $server->name]),
                    action: __('Configure WHM token or enable basic checks'),
                    dueAt: null,
                    serverId: $server->id,
                    subject: $server,
                    url: route('servers.show', $server),
                ));

                return;
            }

            if ($server->telemetry_mode === 'whm' && ! $server->hasWhmCredentials()) {
                $risks->push($this->risk(
                    key: 'whm_token_missing:'.$server->id,
                    category: OperationalRiskCategory::INFRASTRUCTURE,
                    severity: 'warning',
                    title: __('WHM token missing'),
                    description: __(':server cannot sync WHM metrics', ['server' => $server->name]),
                    action: __('Add WHM API token in server settings'),
                    dueAt: null,
                    serverId: $server->id,
                    subject: $server,
                    url: route('servers.edit', $server),
                ));
            }

            if ($server->last_synced_at === null || $server->last_synced_at->lt($staleBefore)) {
                $risks->push($this->risk(
                    key: 'telemetry_stale:'.$server->id,
                    category: OperationalRiskCategory::INFRASTRUCTURE,
                    severity: 'warning',
                    title: __('Telemetry not syncing'),
                    description: __(':server last synced :when', [
                        'server' => $server->name,
                        'when' => $server->last_synced_at?->diffForHumans() ?? __('never'),
                    ]),
                    action: __('Run fleet sync or fix connectivity'),
                    dueAt: $server->last_synced_at,
                    serverId: $server->id,
                    subject: $server,
                    url: route('servers.show', $server),
                ));
            }
        });

        return $risks;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function scanCriticalProviderNotices(): Collection
    {
        return ServerProviderNotice::query()
            ->with('server:id,name')
            ->where('status', 'open')
            ->whereIn('severity', ['critical', 'warning'])
            ->get()
            ->map(fn (ServerProviderNotice $n) => $this->risk(
                key: 'provider_notice:'.$n->id,
                category: OperationalRiskCategory::SERVER,
                severity: $n->severity === 'critical' ? 'critical' : 'warning',
                title: __('Provider notice: :title', ['title' => $n->title]),
                description: $n->body ? \Illuminate\Support\Str::limit($n->body, 120) : __('Open provider notice'),
                action: __('Resolve or acknowledge on server record'),
                dueAt: $n->due_date ?? $n->notice_date,
                serverId: $n->server_id,
                subject: $n,
                url: route('servers.show', $n->server).'#notices',
            ));
    }

    /**
     * @return array<string, mixed>
     */
    private function risk(
        string $key,
        string $category,
        string $severity,
        string $title,
        string $description,
        string $action,
        ?Carbon $dueAt = null,
        ?int $tenantId = null,
        ?int $projectId = null,
        ?int $serverId = null,
        ?int $staffProfileId = null,
        mixed $subject = null,
        ?string $url = null,
    ): array {
        return [
            'key' => $key,
            'category' => $category,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'recommended_action' => $action,
            'due_at' => $dueAt,
            'tenant_id' => $tenantId,
            'project_id' => $projectId,
            'server_id' => $serverId,
            'staff_profile_id' => $staffProfileId,
            'subject_type' => is_object($subject) ? $subject::class : null,
            'subject_id' => is_object($subject) ? $subject->getKey() : null,
            'url' => $url,
            'acknowledged' => false,
        ];
    }
}
