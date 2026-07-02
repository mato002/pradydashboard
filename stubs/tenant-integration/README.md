# Tenant integration stubs

Laravel snippets for **hosted product apps** (MFI, Property, CRM) connecting to Prady Dashboard.

**Start here:** [docs/PRODUCT_APP_INTEGRATION_BRIEF.md](../../docs/PRODUCT_APP_INTEGRATION_BRIEF.md)

## Required files

| File | Purpose |
|------|---------|
| `CheckPradyLicense.php` | Calls dashboard license API; enforces access levels |
| `AuthenticatePradyDashboard.php` | Protects `GET /api/system/info` |
| `SystemInfoController.php` | Dashboard outbound polling contract |
| `license-unavailable.blade.php` | 503 when dashboard unreachable |
| `tenant-suspended.blade.php` | 403 when tenant blocked |
| `config-services-prady-snippet.php` | `config/services.php` block |
| `routes-api-snippet.php` | System info route |
| `bootstrap-app-middleware-snippet.php` | Laravel 11 middleware registration |
| `env-prady-license-snippet.txt` | License `.env` template |
| `env-prady-system-api-snippet.txt` | System info metadata `.env` |

## Optional files

| File | Purpose |
|------|---------|
| `RequirePradyModule.php` | Route middleware for `enabled_modules` |
| `license-warning-banner.blade.php` | Flash banner for `warning` access level |
| `AuthenticatePaymentsGatewayWebhook.php` | Verify gateway webhook signatures |
| `PaymentsGatewayWebhookController.php` | `POST /webhooks/payments-gateway/events` |
| `routes-payments-webhook-snippet.php` | Webhook route registration |
| `cursor-rule-prady-integration.mdc` | Copy to product `.cursor/rules/` |
| `ACCEPTANCE_CHECKS.md` | curl verification checklist |
