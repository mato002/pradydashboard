<?php

namespace App\Support\PublicId;

use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;

class PublicIdRouteCoverage
{
    /**
     * @return list<array{route: string, parameter: string, model: string, protected: bool}>
     */
    public function routes(): array
    {
        return [
            ['route' => 'tenants.show', 'parameter' => 'tenant', 'model' => Tenant::class, 'protected' => true],
            ['route' => 'invoices.show', 'parameter' => 'invoice', 'model' => TenantInvoice::class, 'protected' => true],
            ['route' => 'invoices.preview', 'parameter' => 'invoice', 'model' => TenantInvoice::class, 'protected' => true],
            ['route' => 'invoices.pdf', 'parameter' => 'invoice', 'model' => TenantInvoice::class, 'protected' => true],
            ['route' => 'servers.show', 'parameter' => 'server', 'model' => Server::class, 'protected' => true],
            ['route' => 'support-tickets.show', 'parameter' => 'ticket', 'model' => SupportTicket::class, 'protected' => true],
            ['route' => 'tenants.documents.download', 'parameter' => 'document', 'model' => OperationalDocument::class, 'protected' => true],
            ['route' => 'hosted-projects.show', 'parameter' => 'hostedProject', 'model' => HostedProject::class, 'protected' => true],
            ['route' => 'invoices.payments.suggestions', 'parameter' => 'payment', 'model' => TenantPayment::class, 'protected' => true],
            ['route' => 'invoices.payments.match', 'parameter' => 'payment', 'model' => TenantPayment::class, 'protected' => true],
            ['route' => 'billing.pay', 'parameter' => 'tenant', 'model' => Tenant::class, 'protected' => true],
        ];
    }

    public function isProtected(string $modelClass): bool
    {
        return collect($this->routes())->contains(fn (array $row) => $row['model'] === $modelClass && $row['protected']);
    }
}
