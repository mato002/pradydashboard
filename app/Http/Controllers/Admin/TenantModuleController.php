<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Tenancy\Services\TenantActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantModuleController extends Controller
{
    public function __construct(
        private readonly TenantActivityLogger $activityLogger
    ) {}

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $data = $request->validate([
            'modules' => ['required', 'array'],
            'modules.*.id' => ['required', 'integer', 'exists:license_module_catalog,id'],
            'modules.*.enabled' => ['required', 'boolean'],
        ]);

        $sync = [];
        foreach (array_values($data['modules']) as $row) {
            $sync[(int) $row['id']] = ['enabled' => (bool) $row['enabled']];
        }

        $tenant->licenseModules()->sync($sync);

        $this->activityLogger->log(
            $tenant,
            'tenant.modules_updated',
            'License modules updated',
            ['modules' => $sync],
            $request->user()
        );

        return redirect()
            ->route('tenants.show', ['tenant' => $tenant, 'tab' => 'modules'])
            ->with('status', __('Module entitlements saved.'));
    }
}
