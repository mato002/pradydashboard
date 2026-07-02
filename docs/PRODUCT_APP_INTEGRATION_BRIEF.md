# Product App — Prady Dashboard Integration Brief

**Audience:** Developers and Cursor agents wiring a **hosted product** (MFI, Property, CRM, etc.) to Prady Dashboard.

**Companion docs:**

- Dashboard operator setup: [INTEGRATION_SETUP_GUIDE.md](./INTEGRATION_SETUP_GUIDE.md)
- Architecture: [CONTROL_PLANE_ARCHITECTURE.md](./CONTROL_PLANE_ARCHITECTURE.md)
- Copy-paste stubs: `stubs/tenant-integration/` in this repository

---

## Deployment model

Each product installation is typically **one tenant per server/domain**:

- One `.env` per deployment with fixed `PRADY_TENANT_KEY`
- `domain` in license checks = `request()->getHost()`
- Multi-tenant SaaS on a single domain needs custom design (out of scope for stubs)

---

## Phase A — Dashboard (human, before coding)

Complete on **Prady Dashboard** before changing the product app:

| Step | Action | Output |
|------|--------|--------|
| A1 | Create **Hosted Project** (Infrastructure → Hosted Projects) | `product_key`, `api_token` |
| A2 | Create **Tenant** linked to that project | `tenant_key`, `license_secret`, `external_key`, set `tenant_domain` |
| A3 | Create **project subscription** on tenant | Unlocks Integrations tab |
| A4 | Copy integration kit from Hosted Project show page | Full `.env` block |
| A5 | Generate `PRADY_DASHBOARD_API_TOKEN` (random 64+ chars) | Same value in product `.env` + dashboard Integrations tab |

---

## Phase B — Product app file checklist (Laravel)

Copy from `stubs/tenant-integration/` into the product repo:

| Source stub | Destination | Required |
|-------------|-------------|----------|
| `CheckPradyLicense.php` | `app/Http/Middleware/CheckPradyLicense.php` | Yes |
| `AuthenticatePradyDashboard.php` | `app/Http/Middleware/AuthenticatePradyDashboard.php` | Yes |
| `SystemInfoController.php` | `app/Http/Controllers/Api/SystemInfoController.php` | Yes |
| `RequirePradyModule.php` | `app/Http/Middleware/RequirePradyModule.php` | Optional |
| `license-unavailable.blade.php` | `resources/views/errors/license-unavailable.blade.php` | Yes |
| `tenant-suspended.blade.php` | `resources/views/errors/tenant-suspended.blade.php` | Yes |
| `license-warning-banner.blade.php` | `resources/views/partials/prady-license-warning.blade.php` | Recommended |
| `config-services-prady-snippet.php` | merge into `config/services.php` → `prady` key | Yes |
| `routes-api-snippet.php` | merge into `routes/api.php` | Yes |
| `bootstrap-app-middleware-snippet.php` | merge into `bootstrap/app.php` | Yes |
| `AuthenticatePaymentsGatewayWebhook.php` | `app/Http/Middleware/` | If using M-Pesa webhooks |
| `PaymentsGatewayWebhookController.php` | `app/Http/Controllers/Webhooks/` | If using M-Pesa webhooks |
| `routes-payments-webhook-snippet.php` | merge into `routes/web.php` or `api.php` | If using M-Pesa webhooks |
| `cursor-rule-prady-integration.mdc` | `.cursor/rules/` in **product** repo | Recommended for Cursor |

---

## Phase C — Environment variables (product `.env`)

### Required (license + system info)

```env
PRADY_DASHBOARD_URL=https://dashboard.pradytecai.com
PRADY_PROJECT_API_TOKEN=          # Hosted Project api_token
PRADY_PRODUCT_KEY=                # e.g. property, mfi
PRADY_TENANT_KEY=                 # tenant_key from dashboard
PRADY_LICENSE_SECRET=             # from tenant record
PRADY_DASHBOARD_API_TOKEN=        # you generate; dashboard polls with this
PRADY_LICENSE_CACHE_TTL=600
```

### Recommended (system info metadata)

```env
PRADY_TENANT_CODE=
PRADY_PRODUCT_NAME=
PRADY_BUILD=
PRADY_COMMIT=
PRADY_LAST_DEPLOYED_AT=
```

### Optional (payments gateway events on product app)

```env
PAYMENTS_GATEWAY_WEBHOOK_SECRET=  # matches gateway webhook endpoint secret
```

---

## Phase D — Wiring (Laravel 11)

### D1 — `bootstrap/app.php`

Register middleware aliases and append license check to `web` group. See `bootstrap-app-middleware-snippet.php`.

Exclude from license middleware (adjust paths for your app):

- `up`, `health*`
- `api/system/info`
- `webhooks/payments-gateway/*`

### D2 — `routes/api.php`

```php
Route::middleware(\App\Http\Middleware\AuthenticatePradyDashboard::class)
    ->get('/system/info', \App\Http\Controllers\Api\SystemInfoController::class);
```

### D3 — Layout (warning banner)

In main layout after `<body>`:

```blade
@include('partials.prady-license-warning')
```

### D4 — Module gating (optional)

On routes that need a licensed module:

```php
Route::middleware(['web', 'prady.module:reports'])->group(function () {
    // ...
});
```

Module key must match dashboard `enabled_modules` from license API.

### D5 — Dashboard Integrations tab

After deploy:

1. Tenant → Integrations → add **Tenant system API**
2. URL: `https://{your-domain}/api/system/info`
3. Auth: Bearer token = `PRADY_DASHBOARD_API_TOKEN`
4. Run **Test connection**

---

## Phase E — Acceptance tests

Run these after implementation. Full curl scripts: `stubs/tenant-integration/ACCEPTANCE_CHECKS.md`.

| # | Check | Pass criteria |
|---|-------|---------------|
| E1 | License API from server | `POST /api/v1/license/check` → 200, `allowed: true` |
| E2 | Invalid signature rejected | Same request without `X-Prady-Signature` → 401 |
| E3 | System info | `GET /api/system/info` with dashboard token → 200, `status` + `version` |
| E4 | System info unauthorized | No token → 401 |
| E5 | Web app loads | Browser → 200 (not 503 license-unavailable) |
| E6 | Dashboard poll | Integrations tab → Test connection → pass |
| E7 | License logs | Dashboard License Logs shows checks from this domain |
| E8 | Read-only mode | Set tenant restricted on dashboard → POST blocked, GET allowed |
| E9 | Payments webhook | If configured: gateway test endpoint → 2xx from product URL |

---

## Cursor agent prompt (copy into product project)

Use this after Phase A is done and credentials are in `.env`:

```markdown
Integrate this Laravel product app with Prady Dashboard (control plane).

Read and follow:
1. ../prady-dashboard/docs/PRODUCT_APP_INTEGRATION_BRIEF.md (or paste path)
2. Copy/adapt all required files from ../prady-dashboard/stubs/tenant-integration/

Constraints:
- One tenant per deployment (fixed PRADY_TENANT_KEY in .env)
- Server-to-server HTTP only; no shared sessions with dashboard
- Sign license POST body with HMAC-SHA256 using PRADY_LICENSE_SECRET
- Do not modify unrelated code

Implement:
1. Middleware: CheckPradyLicense on web group (with exclusions for health + system/info + payment webhooks)
2. GET /api/system/info protected by AuthenticatePradyDashboard
3. Error views: license-unavailable, tenant-suspended
4. config/services.php prady block + .env.example entries
5. Optional: RequirePradyModule alias, license warning banner partial
6. Optional: payments gateway webhook route if PAYMENTS_GATEWAY_WEBHOOK_SECRET is set

After code changes, document verification steps from ACCEPTANCE_CHECKS.md.

Credentials are already in .env — do not commit secrets.
```

Adjust `../prady-dashboard/` path if repos are siblings or use absolute paths.

---

## Cursor rule (product repos)

Copy `stubs/tenant-integration/cursor-rule-prady-integration.mdc` to the product app's `.cursor/rules/` so Cursor applies Prady integration conventions automatically.

---

## Troubleshooting (product side)

| Symptom | Fix |
|---------|-----|
| 503 license-unavailable | Dashboard unreachable; check `PRADY_DASHBOARD_URL`, firewall, SSL |
| 401 on license check | Wrong `PRADY_PROJECT_API_TOKEN` or bad HMAC (sign **raw JSON**, not form body) |
| 403 domain mismatch | `tenant_domain` on dashboard must equal `request()->getHost()` |
| 404 tenant not found | `PRADY_TENANT_KEY` or `PRADY_PRODUCT_KEY` mismatch |
| Dashboard can't poll system/info | Wrong `PRADY_DASHBOARD_API_TOKEN`, route not registered, or license middleware blocking `/api/system/info` |
| Warning banner missing | Include `partials.prady-license-warning`; ensure session middleware runs |

---

## Out of scope for stubs

- Multi-tenant single codebase (dynamic tenant_key per request)
- Non-Laravel stacks (use API contracts from INTEGRATION_SETUP_GUIDE.md)
- Payments Gateway treasury setup (dashboard UI on payments.pradytecai.com)
- CI deployment webhooks (configured on dashboard, not product app)
