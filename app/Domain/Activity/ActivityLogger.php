<?php

namespace App\Domain\Activity;

use App\Models\OperationalDocument;
use App\Models\Project;
use App\Models\Server;
use App\Models\StaffProfile;
use App\Models\SupportTicket;
use App\Models\SystemActivityLog;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantProjectInfrastructure;
use App\Models\TenantProjectServiceIntegration;
use App\Models\TenantProjectSubscription;
use App\Models\TenantProjectVersion;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    /** @var list<string> */
    private const SENSITIVE_FRAGMENTS = [
        'password',
        'api_token',
        'api_secret',
        'license_secret',
        'secret',
        '_key',
        'token',
    ];

    /** @var list<string> */
    private const HR_MASKED_FIELDS = [
        'monthly_salary',
        'salary',
    ];

    public function log(
        string $action,
        string $category,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $context = [],
    ): SystemActivityLog {
        $request = request();
        $user = $context['user'] ?? Auth::user();
        unset($context['user']);

        $context = $this->mergeSubjectContext($subject, $context);

        $staffId = $context['staff_profile_id'] ?? null;
        if (! $staffId && $user instanceof User) {
            $staffId = StaffProfile::query()->where('user_id', $user->id)->value('id');
        }

        return SystemActivityLog::query()->create([
            'user_id' => $user instanceof Authenticatable ? $user->getAuthIdentifier() : null,
            'staff_profile_id' => $staffId,
            'actor_name' => $context['actor_name'] ?? ($user instanceof Authenticatable ? $user->name ?? null : null),
            'action' => $action,
            'category' => $category,
            'subject_type' => $context['subject_type'] ?? ($subject ? $subject->getMorphClass() : null),
            'subject_id' => $context['subject_id'] ?? $subject?->getKey(),
            'tenant_id' => $context['tenant_id'] ?? null,
            'project_id' => $context['project_id'] ?? null,
            'server_id' => $context['server_id'] ?? null,
            'invoice_id' => $context['invoice_id'] ?? null,
            'support_ticket_id' => $context['support_ticket_id'] ?? null,
            'description' => $description,
            'old_values' => $this->sanitize($oldValues, $category),
            'new_values' => $this->sanitize($newValues, $category),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? Str::limit((string) $request->userAgent(), 500) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function mergeSubjectContext(?Model $subject, array $context): array
    {
        if (! $subject) {
            return $context;
        }

        $context['subject_type'] ??= $subject->getMorphClass();
        $context['subject_id'] ??= $subject->getKey();

        if ($subject instanceof Tenant) {
            $context['tenant_id'] ??= $subject->id;
            $context['project_id'] ??= $subject->project_id;
        }

        if ($subject instanceof Project) {
            $context['project_id'] ??= $subject->id;
        }

        if ($subject instanceof Server) {
            $context['server_id'] ??= $subject->id;
        }

        if ($subject instanceof TenantInvoice) {
            $context['invoice_id'] ??= $subject->id;
            $context['tenant_id'] ??= $subject->tenant_id;
            $context['project_id'] ??= $subject->projectSubscription?->project_id;
        }

        if ($subject instanceof SupportTicket) {
            $context['support_ticket_id'] ??= $subject->id;
            $context['tenant_id'] ??= $subject->tenant_id;
            $context['project_id'] ??= $subject->project_id;
        }

        if ($subject instanceof TenantProjectSubscription) {
            $context['tenant_id'] ??= $subject->tenant_id;
            $context['project_id'] ??= $subject->project_id;
        }

        if ($subject instanceof TenantProjectInfrastructure || $subject instanceof TenantProjectVersion) {
            $subscription = $subject->relationLoaded('subscription')
                ? $subject->subscription
                : $subject->subscription()->first();
            if ($subscription) {
                $context['tenant_id'] ??= $subscription->tenant_id;
                $context['project_id'] ??= $subscription->project_id;
            }
        }

        if ($subject instanceof TenantProjectServiceIntegration) {
            $subscription = $subject->relationLoaded('subscription')
                ? $subject->subscription
                : $subject->subscription()->first();
            if ($subscription) {
                $context['tenant_id'] ??= $subscription->tenant_id;
                $context['project_id'] ??= $subscription->project_id;
            }
        }

        if ($subject instanceof OperationalDocument) {
            $context['tenant_id'] ??= $subject->tenant_id;
        }

        if ($subject instanceof StaffProfile) {
            $context['staff_profile_id'] ??= $subject->id;
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    public function sanitize(?array $values, string $category): ?array
    {
        if ($values === null) {
            return null;
        }

        return $this->sanitizeRecursive($values, $category);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitizeRecursive(array $values, string $category): array
    {
        $out = [];

        foreach ($values as $key => $value) {
            $keyString = (string) $key;

            if (is_array($value)) {
                $out[$keyString] = $this->sanitizeRecursive($value, $category);

                continue;
            }

            if ($this->isSensitiveKey($keyString)) {
                $out[$keyString] = '***MASKED***';

                continue;
            }

            if ($category === 'hr' && in_array($keyString, self::HR_MASKED_FIELDS, true)) {
                $out[$keyString] = '***MASKED***';

                continue;
            }

            if ($keyString === 'file_path' && is_string($value)) {
                $out[$keyString] = basename($value);

                continue;
            }

            $out[$keyString] = $value;
        }

        return $out;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        foreach (self::SENSITIVE_FRAGMENTS as $fragment) {
            if (str_contains($lower, $fragment)) {
                return true;
            }
        }

        return $lower === 'key' || str_ends_with($lower, '_key');
    }

    /**
     * @param  array<string, mixed>  $model
     * @return array<string, mixed>
     */
    public function modelChanges(Model $model, array $only = []): array
    {
        $changes = $model->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if ($only !== []) {
            $changes = Arr::only($changes, $only);
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        return ['old' => $old, 'new' => $changes];
    }
}
