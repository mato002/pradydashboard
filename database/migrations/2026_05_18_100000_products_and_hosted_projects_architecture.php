<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** @var array<string, string> table => onDelete (cascade|null) */
    private array $projectFkTables = [
        'tenants' => 'cascade',
        'license_check_logs' => 'null',
        'support_tickets' => 'null',
        'project_deployments' => 'cascade',
        'managed_domains' => 'null',
        'backups' => 'null',
        'deployment_webhook_events' => 'null',
        'deployment_ops_events' => 'null',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->string('default_billing_model')->default('subscription');
            $table->string('default_license_mode')->default('module');
            $table->timestamps();
            });
        }

        if (Schema::hasTable('projects') && ! Schema::hasTable('hosted_projects')) {
            Schema::rename('projects', 'hosted_projects');
        }

        if (Schema::hasTable('hosted_projects')) {
            Schema::table('hosted_projects', function (Blueprint $table) {
                if (! Schema::hasColumn('hosted_projects', 'product_id')) {
                    $table->foreignId('product_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('hosted_projects', 'environment')) {
                    $table->string('environment')->default('production')->after('domain');
                }
                if (! Schema::hasColumn('hosted_projects', 'cpanel_username')) {
                    $table->string('cpanel_username')->nullable()->after('database_name');
                }
            });

            if (Schema::hasColumn('hosted_projects', 'technology_stack') && ! Schema::hasColumn('hosted_projects', 'stack')) {
                Schema::table('hosted_projects', function (Blueprint $table) {
                    $table->renameColumn('technology_stack', 'stack');
                });
            }
        }

        $this->migrateProductsAndHostedProjects();
        $this->renameProjectForeignKeys();

        if (Schema::hasColumn('hosted_projects', 'product_slug')) {
            foreach (['hosted_projects_product_slug_unique', 'projects_product_slug_unique'] as $index) {
                if (DB::getDriverName() === 'sqlite') {
                    DB::statement("DROP INDEX IF EXISTS {$index}");
                } else {
                    try {
                        DB::statement("ALTER TABLE hosted_projects DROP INDEX `{$index}`");
                    } catch (\Throwable) {
                    }
                }
            }

            Schema::table('hosted_projects', function (Blueprint $table) {
                $drop = array_filter([
                    Schema::hasColumn('hosted_projects', 'product_slug') ? 'product_slug' : null,
                    Schema::hasColumn('hosted_projects', 'monthly_revenue') ? 'monthly_revenue' : null,
                    Schema::hasColumn('hosted_projects', 'monthly_cost') ? 'monthly_cost' : null,
                    Schema::hasColumn('hosted_projects', 'version') ? 'version' : null,
                ]);
                if ($drop !== []) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (! Schema::hasColumn('tenants', 'product_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('external_key')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('tenants', 'domain')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('domain')->nullable()->after('company_name');
            });
        }

        $this->migrateTenants();
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'domain']);
        });

        $this->revertProjectForeignKeys();

        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->renameColumn('stack', 'technology_stack');
        });

        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->string('product_slug', 80)->nullable()->unique();
            $table->string('version')->nullable();
            $table->decimal('monthly_revenue', 14, 2)->nullable();
            $table->decimal('monthly_cost', 14, 2)->nullable();
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'environment', 'cpanel_username']);
        });

        Schema::rename('hosted_projects', 'projects');
        Schema::dropIfExists('products');
    }

    private function migrateProductsAndHostedProjects(): void
    {
        $productIdsBySlug = [];

        foreach (DB::table('hosted_projects')->orderBy('id')->get() as $row) {
            $slug = $this->resolveProductSlug($row);

            if (! isset($productIdsBySlug[$slug])) {
                $productIdsBySlug[$slug] = DB::table('products')->insertGetId([
                    'name' => $this->humanizeSlug($slug),
                    'slug' => $slug,
                    'description' => $row->description,
                    'category' => 'saas',
                    'status' => 'active',
                    'default_billing_model' => 'subscription',
                    'default_license_mode' => 'module',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('hosted_projects')->where('id', $row->id)->update([
                'product_id' => $productIdsBySlug[$slug],
                'environment' => $row->status === 'maintenance' ? 'staging' : 'production',
                'product_key' => $row->product_key ?: $slug,
            ]);
        }
    }

    private function migrateTenants(): void
    {
        foreach (DB::table('tenants')->orderBy('id')->get() as $tenant) {
            $hosted = DB::table('hosted_projects')->where('id', $tenant->hosted_project_id)->first();

            DB::table('tenants')->where('id', $tenant->id)->update([
                'product_id' => $hosted?->product_id,
                'domain' => $tenant->tenant_domain,
            ]);
        }
    }

    private function renameProjectForeignKeys(): void
    {
        foreach ($this->projectFkTables as $table => $onDelete) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'hosted_project_id')) {
                continue;
            }

            if (! Schema::hasColumn($table, 'project_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['project_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('project_id', 'hosted_project_id');
            });

            Schema::table($table, function (Blueprint $blueprint) use ($onDelete) {
                $foreign = $blueprint->foreign('hosted_project_id')->references('id')->on('hosted_projects');
                $onDelete === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
            });
        }
    }

    private function revertProjectForeignKeys(): void
    {
        foreach ($this->projectFkTables as $table => $onDelete) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'hosted_project_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['hosted_project_id']);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('hosted_project_id', 'project_id');
            });

            Schema::table($table, function (Blueprint $blueprint) use ($onDelete) {
                $foreign = $blueprint->foreign('project_id')->references('id')->on('projects');
                $onDelete === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
            });
        }
    }

    private function resolveProductSlug(object $row): string
    {
        $candidates = [
            $row->product_slug ?? null,
            $row->product_key ?? null,
            Str::slug($row->name),
        ];

        foreach ($candidates as $candidate) {
            if (filled($candidate)) {
                return Str::slug(str_replace('_', '-', (string) $candidate));
            }
        }

        return 'product-'.$row->id;
    }

    private function humanizeSlug(string $slug): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $slug));
    }
};
