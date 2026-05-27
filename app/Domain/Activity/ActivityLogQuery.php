<?php

namespace App\Domain\Activity;

use App\Models\SystemActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActivityLogQuery
{
    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'q' => $request->query('q'),
            'category' => $request->query('category'),
            'actor' => $request->query('actor'),
            'tenant_id' => $request->query('tenant_id'),
            'project_id' => $request->query('project_id'),
            'server_id' => $request->query('server_id'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if ($category = $filters['category'] ?? null) {
            $query->where('category', $category);
        }

        if ($tenantId = $filters['tenant_id'] ?? null) {
            $query->where('tenant_id', $tenantId);
        }

        if ($projectId = $filters['project_id'] ?? null) {
            $query->where('hosted_project_id', $projectId);
        }

        if ($serverId = $filters['server_id'] ?? null) {
            $query->where('server_id', $serverId);
        }

        if ($actor = trim((string) ($filters['actor'] ?? ''))) {
            $query->where(function (Builder $q) use ($actor): void {
                $q->where('actor_name', 'like', "%{$actor}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$actor}%"))
                    ->orWhereHas('staffProfile', fn (Builder $s) => $s->where('full_name', 'like', "%{$actor}%"));
            });
        }

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($from = $filters['from'] ?? null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $filters['to'] ?? null) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage = 25, bool $applyRbacScope = true): LengthAwarePaginator
    {
        $query = SystemActivityLog::query()
            ->with(['user:id,name', 'staffProfile:id,full_name', 'tenant:id,company_name', 'project:id,name', 'server:id,name'])
            ->orderByDesc('created_at');

        if ($applyRbacScope) {
            $query = app(\App\Domain\Rbac\RbacScopeFilter::class)->applyActivityLogScope($query);
        }

        return $this->apply($query, $filters)->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, SystemActivityLog>
     */
    public function recent(int $limit = 10, ?array $filters = null): Collection
    {
        $query = SystemActivityLog::query()
            ->with(['user:id,name', 'staffProfile:id,full_name', 'tenant:id,company_name'])
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($filters) {
            $this->apply($query, $filters);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, SystemActivityLog>
     */
    public function forContext(
        ?int $tenantId = null,
        ?int $projectId = null,
        ?int $serverId = null,
        ?int $invoiceId = null,
        ?int $supportTicketId = null,
        ?int $staffProfileId = null,
        int $limit = 20,
    ): Collection {
        $query = SystemActivityLog::query()
            ->with(['user:id,name', 'staffProfile:id,full_name'])
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($projectId) {
            $query->where('hosted_project_id', $projectId);
        }

        if ($serverId) {
            $query->where('server_id', $serverId);
        }

        if ($invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        if ($supportTicketId) {
            $query->where('support_ticket_id', $supportTicketId);
        }

        if ($staffProfileId) {
            $query->where(function (Builder $q) use ($staffProfileId): void {
                $q->where('staff_profile_id', $staffProfileId)
                    ->orWhere(fn (Builder $inner) => $inner
                        ->where('category', 'hr')
                        ->where('subject_type', 'staff_profile')
                        ->where('subject_id', $staffProfileId));
            });
        }

        return $query->get();
    }
}
