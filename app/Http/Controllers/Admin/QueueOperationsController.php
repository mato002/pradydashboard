<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Rbac\RbacGuard;
use App\Http\Controllers\Controller;
use App\Services\Queue\QueueMonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class QueueOperationsController extends Controller
{
    public function index(QueueMonitorService $monitor, RbacGuard $rbac): View
    {
        $user = auth()->user();
        $canManageFailedJobs = $user && $rbac->can($user, 'monitoring.sync');

        return view('admin.monitoring.queues', [
            'snapshot' => $monitor->snapshot(),
            'canManageFailedJobs' => $canManageFailedJobs,
        ]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('status', __('Failed job queued for retry.'));
    }

    public function forget(string $uuid): RedirectResponse
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('status', __('Failed job removed from retry center.'));
    }

    public function failedJobDetails(string $uuid, QueueMonitorService $monitor): \Illuminate\Http\JsonResponse
    {
        $details = $monitor->failedJobDetails($uuid);

        abort_if($details === null, 404);

        return response()->json($details);
    }
}
