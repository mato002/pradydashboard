<?php

use App\Models\Concerns\HasPublicId;
use App\Models\HostedProject;
use App\Models\OperationalDocument;
use App\Models\Server;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantPayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, class-string<HasPublicId>>
     */
    private array $models = [
        'tenants' => Tenant::class,
        'tenant_invoices' => TenantInvoice::class,
        'hosted_projects' => HostedProject::class,
        'servers' => Server::class,
        'tenant_payments' => TenantPayment::class,
        'support_tickets' => SupportTicket::class,
        'operational_documents' => OperationalDocument::class,
    ];

    public function up(): void
    {
        foreach (array_keys($this->models) as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('public_id', 32)->nullable()->after('id');
            });
        }

        foreach ($this->models as $table => $modelClass) {
            $modelClass::query()
                ->whereNull('public_id')
                ->orderBy('id')
                ->each(function ($model) use ($modelClass): void {
                    $modelClass::query()
                        ->whereKey($model->getKey())
                        ->update(['public_id' => $modelClass::generateUniquePublicId()]);
                });
        }

        foreach (array_keys($this->models) as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unique('public_id');
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->models) as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropUnique(['public_id']);
                $table->dropColumn('public_id');
            });
        }
    }
};
