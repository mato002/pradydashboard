<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Known resource prefixes to strip. Token portion is preserved unchanged.
     *
     * @var array<string, string>
     */
    private array $tables = [
        'tenants' => 'tnt_',
        'tenant_invoices' => 'inv_',
        'hosted_projects' => 'prj_',
        'servers' => 'srv_',
        'tenant_payments' => 'pay_',
        'support_tickets' => 'tkt_',
        'operational_documents' => 'odoc_',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $prefix) {
            DB::table($table)
                ->where('public_id', 'like', $prefix.'%')
                ->orderBy('id')
                ->each(function (object $row) use ($table, $prefix): void {
                    $token = substr((string) $row->public_id, strlen($prefix));

                    if ($token === '' || $token === $row->public_id) {
                        return;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['public_id' => $token]);
                });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $prefix) {
            DB::table($table)
                ->whereNotNull('public_id')
                ->where('public_id', 'not like', $prefix.'%')
                ->orderBy('id')
                ->each(function (object $row) use ($table, $prefix): void {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['public_id' => $prefix.$row->public_id]);
                });
        }
    }
};
