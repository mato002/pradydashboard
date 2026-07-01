<?php

namespace App\Http\Middleware;

use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyNumericPublicIds
{
    /**
     * Route parameter names mapped to models exposed in public/admin URLs.
     *
     * @var array<string, class-string<Model>>
     */
    private const BINDINGS = [
        'tenant' => Tenant::class,
        'invoice' => TenantInvoice::class,
        'server' => Server::class,
        'payment' => TenantPayment::class,
        'ticket' => SupportTicket::class,
        'hostedProject' => HostedProject::class,
        'project' => HostedProject::class,
        'document' => OperationalDocument::class,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $route = $request->route();
        if ($route === null || $route->getName() === null) {
            return $next($request);
        }

        // Signed billing links bind to the exact URL path; do not rewrite numeric tenant ids.
        if ($route->getName() === 'billing.pay') {
            return $next($request);
        }

        $parameters = $route->parameters();
        $changed = false;

        foreach (self::BINDINGS as $param => $modelClass) {
            if (! array_key_exists($param, $parameters)) {
                continue;
            }

            $value = $parameters[$param];
            if ($value instanceof Model) {
                continue;
            }

            if (! $modelClass::isLegacyNumericId($value)) {
                continue;
            }

            $model = $modelClass::query()->whereKey((int) $value)->first();
            if ($model === null || blank($model->public_id)) {
                continue;
            }

            $parameters[$param] = $model->public_id;
            $changed = true;
        }

        if (! $changed) {
            return $next($request);
        }

        $target = route($route->getName(), $parameters, false);
        $query = $request->getQueryString();

        if ($query !== null && $query !== '') {
            $target .= '?'.$query;
        }

        if ($target === $request->getRequestUri()) {
            return $next($request);
        }

        return redirect($target, 301);
    }
}
