# Prady Dashboard — Control Plane Architecture

## What this system is

**Prady Dashboard** (`dashboard.pradytecai.com`) is the **master control system** for:

- Tenant registry
- Subscriptions & billing status
- License enforcement
- Access control & module entitlements
- API audit logs

It is **not** a WHM/cPanel replacement. It does **not** host, provision, or directly control servers.

## What stays separate

Each product/tenant system runs on its **own domain** in WHM/cPanel, for example:

| URL | Role |
|-----|------|
| `dashboard.pradytecai.com` | Central control plane (this app) |
| `property.pradytecai.com` | Property SaaS product |
| `mfi.pradytecai.com` | MFI product |
| `crm.pradytecai.com` | CRM product |
| `clientdomain.com` | External tenant domain |

Communication is **server-to-server HTTP API only**.

## License check flow

```
Tenant user → Product app (property/mfi/crm)
    → CheckPradyLicense middleware
    → POST https://dashboard.pradytecai.com/api/v1/license/check
    → Prady Dashboard evaluates tenant + subscription + access control
    → JSON: allowed, tenant_status, access_level, enabled_modules
    → Product app enforces (full / warning / read_only / blocked)
```

**Never:**

- Share Laravel sessions across domains
- Query another app's database from the dashboard
- Rely on iframe or frontend-only checks

## API

### `POST /api/v1/license/check`

**Headers:**

- `Authorization: Bearer {project_api_token}` — identifies the product (from Hosted Projects)
- `X-Prady-Signature` — HMAC-SHA256 of the **raw JSON body** using the tenant's `license_secret` (required when secret is set)

**Body:**

```json
{
  "tenant_key": "abc-properties",
  "product_key": "property",
  "domain": "property.pradytecai.com"
}
```

**Responses:** see `TenantLicenseFormatter::toPublicApiArray()` — `allowed`, `tenant_status`, `access_level`, `message`, `enabled_modules`.

## Access levels (enforced in each product app)

| access_level | allowed | Product behavior |
|--------------|---------|------------------|
| `full` | yes | Normal operation |
| `warning` | yes | Show payment banner; allow access |
| `read_only` | yes | Login OK; block POST/PUT/PATCH/DELETE |
| `blocked` | no | Block login / dashboard |

## Tenant app integration

Copy from `stubs/tenant-integration/`:

- `CheckPradyLicense.php` → `app/Http/Middleware/`
- Register middleware on `web` routes
- Add `config/services.php` prady block (see snippet)

## Dashboard modules (data model)

| Module | Model / table |
|--------|----------------|
| Products | `projects` (+ `product_key`, `product_slug`, `base_url`) |
| Tenants | `tenants` (+ `tenant_key`, `license_secret`, `access_level`) |
| Subscriptions | `tenant_subscriptions` |
| Access controls | `tenant_access_controls` |
| License logs | `license_check_logs` |
| Invoices / payments | `tenant_invoices`, `tenant_payments` |

Infrastructure screens (servers, SSL, backups) are **reference/ops context only** — not the core mission.
