<?php

namespace App\Support\PublicId;

class PublicIdBladeRouteScanner
{
    /**
     * High-risk Blade views that must not pass raw numeric model ids into public routes.
     *
     * @var list<string>
     */
    private const HIGH_RISK_VIEWS = [
        'resources/views/admin/invoices/partials/register-table.blade.php',
        'resources/views/admin/invoices/partials/payments-inbox.blade.php',
        'resources/views/admin/invoices/show.blade.php',
        'resources/views/admin/tenants/index.blade.php',
        'resources/views/admin/servers/index.blade.php',
        'resources/views/admin/support-tickets/index.blade.php',
        'resources/views/admin/support-tickets/show.blade.php',
        'resources/views/admin/tenants/partials/ops/documents.blade.php',
        'resources/views/admin/payments/index.blade.php',
        'resources/views/admin/dashboard.blade.php',
    ];

    /**
     * @var list<string>
     */
    private const FORBIDDEN_PATTERNS = [
        "/route\\(['\"]tenants\\.show['\"],\\s*\\\$tenant->id\\)/",
        "/route\\(['\"]invoices\\.(show|preview|pdf)['\"],\\s*\\\$invoice->id\\)/",
        "/route\\(['\"]servers\\.show['\"],\\s*\\\$server->id\\)/",
        "/route\\(['\"]support-tickets\\.show['\"],\\s*\\\$ticket->id\\)/",
        "/route\\(['\"]tenants\\.documents\\.download['\"][^)]*\\\$document->id\\)/",
    ];

    /**
     * @return list<array{file: string, pattern: string, line: int, snippet: string}>
     */
    public function violations(): array
    {
        $violations = [];

        foreach (self::HIGH_RISK_VIEWS as $relativePath) {
            $path = base_path($relativePath);
            if (! is_file($path)) {
                continue;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $index => $line) {
                foreach (self::FORBIDDEN_PATTERNS as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $violations[] = [
                            'file' => $relativePath,
                            'pattern' => $pattern,
                            'line' => $index + 1,
                            'snippet' => trim($line),
                        ];
                    }
                }
            }
        }

        return $violations;
    }

    public function isClean(): bool
    {
        return $this->violations() === [];
    }
}
