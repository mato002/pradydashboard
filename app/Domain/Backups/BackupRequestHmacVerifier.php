<?php

namespace App\Domain\Backups;

use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class BackupRequestHmacVerifier
{
    public function verify(Request $request, Project $project): void
    {
        $tenant = $this->resolveTenant($project, $request);

        if ($tenant === null || ! filled($tenant->license_secret)) {
            return;
        }

        $signature = $request->header('X-Prady-Signature')
            ?? $request->header('X-License-Signature');

        if (! is_string($signature) || $signature === '') {
            throw new HttpException(401, 'Missing request signature.');
        }

        $expected = hash_hmac('sha256', $request->getContent(), (string) $tenant->license_secret);

        if (! hash_equals($expected, $signature)) {
            throw new HttpException(401, 'Invalid request signature.');
        }
    }

    private function resolveTenant(Project $project, Request $request): ?Tenant
    {
        $tenantKey = $request->input('tenant_key');
        if (! is_string($tenantKey) || $tenantKey === '') {
            return null;
        }

        return Tenant::query()
            ->where('tenant_key', $tenantKey)
            ->where('hosted_project_id', $project->id)
            ->first();
    }
}
