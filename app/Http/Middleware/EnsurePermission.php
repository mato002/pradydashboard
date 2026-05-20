<?php

namespace App\Http\Middleware;

use App\Support\Rbac\Rbac;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $scope = array_filter([
            'tenant_id' => $request->route('tenant')?->getKey() ?? $request->route('tenant'),
            'project_id' => $request->route('project')?->getKey() ?? $request->route('project'),
            'server_id' => $request->route('server')?->getKey() ?? $request->route('server'),
        ]);

        if (! Rbac::can($permission, $scope)) {
            abort(403, __('You do not have permission to perform this action with your current active role.'));
        }

        return $next($request);
    }
}
