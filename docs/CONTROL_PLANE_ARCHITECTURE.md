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

## Payments Gateway control plane

**Prady Dashboard** also acts as the operational control plane for **payments.pradytecai.com**. It does **not** store payment data or process M-Pesa transactions locally. All reads and actions go through `PaymentsGatewayClient` using:

| Env var | Purpose |
|---------|---------|
| `PAYMENTS_GATEWAY_URL` | Base URL (default `https://payments.pradytecai.com`) |
| `PAYMENTS_GATEWAY_ADMIN_TOKEN` | Bearer token for admin API routes |
| `PAYMENTS_GATEWAY_TIMEOUT` | HTTP timeout seconds |
| `PAYMENTS_GATEWAY_RETRY_ATTEMPTS` | Retry count for transient failures |

**UI location:** Settings → API & Integrations → Payments Gateway

**Permissions:**

| Code | Purpose |
|------|---------|
| `payments_gateway.view` | Read-only monitoring (tenants, transactions, readiness, health) |
| `payments_gateway.manage` | Create/edit/suspend records, redispatch webhooks |

**Production readiness (Phase 6B):** The dashboard calls `GET /api/v1/operations/production-readiness` on the payments gateway. Optional query params:

- `paybill_account_uuid` — include Daraja and callback diagnostics for a PayBill account
- `test_oauth=true` — attempt live Daraja OAuth as part of the report

The report covers environment, database, queue, Daraja, callbacks, workers, security, and treasury checks. The dashboard derives issues (`fail`), warnings (`warn`), and recommendations (`skip` + optional-section messages) from the gateway response — checks are **not** duplicated locally.

**Go-live dry run (Phase 6D):** The dashboard calls `GET /api/v1/operations/go-live-dry-run/{paybill_account_uuid}` when an operator submits the dry-run form. Optional query params:

- `skip_oauth=1` — skip live Daraja OAuth in the report
- `strict=1` — treat warnings as blocking issues

The gateway returns `overall_status` (`pass`, `warn`, or `blocked`), account metadata, `blocking_issues`, `warnings`, `checklist_items`, and `next_steps`. The dashboard groups checklist items into Environment, Daraja, Callbacks, Queue, Workers, Security, Treasury, and Webhooks panels — no checks are run locally.

**Treasury tenant mapping:** Dashboard tenants (`tenants` table) are the source of truth for tenant identity. Payments Gateway does **not** create standalone tenant records in the UI anymore. Linkage is stored on each dashboard tenant:

| Column | Purpose |
|--------|---------|
| `payments_gateway_tenant_uuid` | UUID of the tenant on payments.pradytecai.com |
| `payments_gateway_linked_at` | Last successful link/sync timestamp |
| `payments_gateway_status` | Gateway linkage status (`active`, `unlinked`, `unreachable`, etc.) |

`PaymentsGatewayTenantLinkService` handles link (create or adopt existing gateway tenant by `external_tenant_id`), sync (PATCH gateway tenant from dashboard fields), unlink (clear local linkage only), and linkage health verification. One dashboard tenant maps to at most one gateway tenant UUID.

**Tenant linkage model**

- Dashboard routes use the dashboard tenant **ID** (`/settings/api-integrations/payments-gateway/tenants/{tenant}`), not the gateway tenant UUID.
- Linking sends the dashboard tenant `external_key` as the gateway `external_tenant_id` so an existing gateway mirror can be adopted.
- Unlinking clears only the dashboard linkage columns; treasury records on payments.pradytecai.com are **not** deleted.

**Treasury mapping model**

The linked-tenant treasury mapping page is the operational hub for gateway resources tied to a dashboard tenant:

| Section | Gateway data (via API) | Dashboard behavior |
|---------|------------------------|-------------------|
| Link status | Gateway tenant UUID, status | Shows local linkage columns + sync/unlink actions |
| Payment profiles | `GET /tenants/{uuid}/payment-profiles` | Create profile sends gateway tenant UUID internally |
| PayBill accounts | Per-profile paybill list | Grouped by profile; dry-run/readiness links |
| Webhook endpoints | Per-profile webhook list | Highlights expected tenant listener URL `https://{tenant_primary_domain}/webhooks/payments-gateway/events` |
| Gateway API keys | Per-profile API keys | Generate/revoke on gateway; raw key shown once only |
| Setup checklist | Derived from loaded gateway data | Pass/fail/pending for go-live prerequisites |

**What the dashboard stores vs the gateway**

| Stored on dashboard | Stored on payments.pradytecai.com |
|---------------------|-----------------------------------|
| Tenant identity (`company_name`, `tenant_key`, `tenant_domain`, `external_key`) | Gateway tenant mirror (`uuid`, `external_tenant_id`, status) |
| Linkage columns (`payments_gateway_tenant_uuid`, `payments_gateway_linked_at`, `payments_gateway_status`) | Payment profiles, PayBill accounts, Daraja credentials |
| — | Webhook endpoints, gateway API keys, transactions, callback logs |

The dashboard never persists M-Pesa credentials, payment profiles, PayBills, webhooks, or API keys locally. If the gateway is unavailable, the mapping page shows the last local linkage state and a warning that treasury resources could not be loaded.

**Treasury mapping page actions (Phase 6G)**

| Action | Behavior |
|--------|----------|
| Production Readiness (PayBill row / checklist) | Opens `/production-readiness?paybill_account_uuid={uuid}&run=1` and runs the gateway readiness report in that PayBill context |
| Go-Live Dry Run (PayBill row / checklist) | Opens `/go-live-dry-run?paybill_account_uuid={uuid}&run=1` and runs the gateway dry-run report in that PayBill context |
| Test endpoint (webhook row) | `POST /api/v1/webhook-endpoints/{uuid}/test` via dashboard client; redirects back to mapping page with success or error |
| Checklist links | Webhook/API key items anchor to `#treasury-webhooks` / `#treasury-api-keys`; readiness/dry-run items deep-link with `run=1` |

**Webhook endpoint test lifecycle (Phase 6H)**

1. Operator links dashboard tenant to gateway tenant and confirms webhook endpoint URL + secret on the mapping page.
2. Operator clicks **Test endpoint** on a webhook row → dashboard calls `POST /api/v1/webhook-endpoints/{endpoint_uuid}/test` with the admin token.
3. Gateway validates endpoint is **active**, builds a signed `gateway.webhook.test` payload, POSTs synchronously to the tenant URL, and persists `webhook_events` + `webhook_deliveries` (never fakes success).
4. Dashboard surfaces gateway `message` as flash success/error on the mapping page.
5. Before go-live, run **Production Readiness** and **Go-Live Dry Run** for the PayBill account; use webhook test to confirm the tenant actually receives and verifies signed events.

**Relationship with go-live readiness**

| Check | Go-live dry run | Webhook endpoint test |
|-------|-----------------|------------------------|
| Active endpoint configured | Yes | Yes (precondition) |
| Secret configured | Yes | Yes (precondition) |
| Tenant URL HTTP reachable | No | Yes |
| Signature accepted by tenant | No | Yes (tenant must return 2xx) |
| Creates delivery audit trail | No | Yes |

**Gateway contract (implemented on payments.pradytecai.com)**

`POST /api/v1/webhook-endpoints/{endpoint}/test` returns `success: true` with `delivery_status: success` only when the tenant responds HTTP 2xx. Failures return HTTP `422` with `success: false`, `delivery_status: failed`, and `failure_reason`. Inspect persisted deliveries via dashboard **Webhook Deliveries** or `GET /api/v1/webhook-deliveries`.

**Operations Console (Phase 7A)**

The treasury operations console is a consolidated monitoring page at `/settings/api-integrations/payments-gateway/operations-console` (permission `payments_gateway.view`). It reads all data from payments.pradytecai.com via `PaymentsGatewayClient` — no payment or callback data is stored locally.

| Section | Primary gateway APIs | Fallback when summary API missing |
|---------|---------------------|-----------------------------------|
| Live Transactions | `GET /api/v1/operations/transactions/summary` | `GET /api/v1/transactions` (recent list + derived counts) |
| Callback Health | `GET /api/v1/operations/callback-logs/summary` | `GET /api/v1/callback-logs` (recent + filtered failed/duplicate) |
| Webhook Health | `GET /api/v1/operations/webhooks/summary` | `GET /api/v1/webhook-events`, `GET /api/v1/webhook-deliveries` |
| Queue Health | `GET /api/v1/operations/queue/overview` | Empty state with missing-endpoint notice |
| Reconciliation Snapshot | `GET /api/v1/operations/reconciliation/runs`, `GET /api/v1/operations/reconciliation/unmatched` | Empty tables + missing-endpoint notice |
| Treasury Alerts | `GET /api/v1/operations/treasury-alerts` | Empty table + missing-endpoint notice |
| Go-Live & Readiness | `GET /api/v1/operations/readiness/status` | Links to existing Production Readiness / Go-Live Dry Run pages |

If the gateway health check fails, the page renders with: *"Operations console could not load because payments.pradytecai.com is unavailable."* When individual operations summary endpoints return HTTP 404, the dashboard lists the missing endpoint and continues with fallbacks where available.

**Operations Console UX (Phase 7C)**

The console is an incident-response workspace for treasury operators:

| UX surface | Purpose |
|------------|---------|
| Operations health banner | Green/yellow/red posture for gateway, queue, alerts, webhooks, dead letters, reconciliation |
| Incident panels | Expandable cards for failed webhooks, dead letters, failed callbacks, unmatched transactions, critical alerts |
| Live activity stream | Newest-first timeline of STK/B2C, callbacks, alerts, webhook failures |
| Queue worker visibility | Active/stale/offline workers with last heartbeat age |
| Reconciliation urgency | Highlights unmatched count, large variances, settlement variance |
| Quick actions | Redispatch webhook, replay dead letter, retry callback, acknowledge/resolve alert (`payments_gateway.manage`) |

**Incident response flow**

1. Open **Operations Console** and review the health banner (red/yellow/green).
2. Expand the highest-severity incident panel (dead letters → critical alerts → failed webhooks/callbacks → unmatched reconciliation).
3. Use tenant mapping links to identify affected dashboard tenant, profile, PayBill, and webhook endpoint.
4. Run a safe quick action with confirmation (requires `payments_gateway.manage`).
5. Verify recovery in the activity stream and downstream monitoring pages (Transactions, Callback Logs, Webhook Deliveries).
6. Escalate unresolved reconciliation or settlement variance to treasury reconciliation workflows on the gateway.

**Operational hierarchy**

| Priority | Signal | Typical action |
|----------|--------|----------------|
| P0 | Dead letters, critical treasury alerts | Replay dead letter or acknowledge/resolve alert before new traffic |
| P1 | Failed webhook deliveries, failed callbacks | Redispatch webhook or retry callback processing |
| P2 | Unmatched reconciliation, queue degradation | Review reconciliation snapshot; restore workers |
| P3 | Pending transactions, readiness warnings | Monitor; run Production Readiness / Go-Live Dry Run |

Quick actions proxy to payments.pradytecai.com:

| Action | Gateway API |
|--------|-------------|
| Redispatch webhook delivery | `POST /api/v1/webhook-deliveries/{uuid}/redispatch` |
| Replay dead letter | `POST /api/v1/operations/queue/dead-letters/{uuid}/replay` |
| Discard dead letter | `POST /api/v1/operations/queue/dead-letters/{uuid}/discard` |
| Retry callback | `POST /api/v1/operations/queue/callbacks/{uuid}/retry` |
| Acknowledge alert | `POST /api/v1/treasury/alerts/{uuid}/acknowledge` |
| Resolve alert | `POST /api/v1/treasury/alerts/{uuid}/resolve` |

If a quick-action API returns HTTP 404, the console shows *"Operation API not available yet."*

**Operations Console action wiring (Phase 7E — dashboard control plane)**

Phase 7E wires all Operations Console quick actions to the live operations APIs implemented on payments.pradytecai.com (Phase 7D). The dashboard never stores gateway queue, callback, or alert data locally — every list read and remediation action proxies through `PaymentsGatewayClient`.

| Client method | Gateway API | Dashboard route |
|---------------|-------------|-----------------|
| `listDeadLetters()` | `GET /api/v1/operations/queue/dead-letters` | (console read) |
| `getDeadLetter($uuid)` | `GET /api/v1/operations/queue/dead-letters/{uuid}` | (available for detail flows) |
| `replayDeadLetter($uuid)` | `POST /api/v1/operations/queue/dead-letters/{uuid}/replay` | `POST .../operations-console/dead-letters/{uuid}/replay` |
| `discardDeadLetter($uuid)` | `POST /api/v1/operations/queue/dead-letters/{uuid}/discard` | `POST .../operations-console/dead-letters/{uuid}/discard` |
| `listQueueWorkers()` | `GET /api/v1/operations/queue/workers` | (console read) |
| `retryCallback($uuid)` | `POST /api/v1/operations/queue/callbacks/{uuid}/retry` | `POST .../operations-console/callback-logs/{uuid}/retry` |
| `acknowledgeTreasuryAlert($uuid, $payload)` | `POST /api/v1/treasury/alerts/{uuid}/acknowledge` | `POST .../operations-console/treasury-alerts/{uuid}/acknowledge` |
| `resolveTreasuryAlert($uuid, $payload)` | `POST /api/v1/treasury/alerts/{uuid}/resolve` | `POST .../operations-console/treasury-alerts/{uuid}/resolve` |

**Discard dead letter behavior**

Discarding marks the dead letter as handled on the gateway. It will not be replayed automatically. The dashboard shows a confirmation warning before submitting: *"Discarding a dead letter marks it as handled and it will not be replayed automatically."*

Incident panels render live gateway fields when APIs return HTTP 200:

| Panel | Live fields |
|-------|-------------|
| Dead letters | type, queue, status, age, replay, discard |
| Queue workers | active/stale/offline counts, worker name, queue names, last seen, age seconds |
| Failed callbacks | retry action with success/failure flash |
| Treasury alerts | acknowledge/resolve with optional comments |

Placeholder text (*"Operation API not available yet."*) appears only when the corresponding gateway list or action endpoint returns HTTP 404.

**Bulk incident remediation (Phase 7F — dashboard control plane)**

Operators can select multiple incident rows in the Operations Console and run one bulk action per panel. The dashboard delegates each UUID to payments.pradytecai.com through `OperationsBulkActionService` and `PaymentsGatewayClient` — no gateway incident data is persisted locally.

| Bulk action | Gateway delegation (per UUID) |
|-------------|-------------------------------|
| `dead_letters.replay` | `POST /api/v1/operations/queue/dead-letters/{uuid}/replay` |
| `dead_letters.discard` | `POST /api/v1/operations/queue/dead-letters/{uuid}/discard` |
| `callbacks.retry` | `POST /api/v1/operations/queue/callbacks/{uuid}/retry` |
| `alerts.acknowledge` | `POST /api/v1/treasury/alerts/{uuid}/acknowledge` |
| `alerts.resolve` | `POST /api/v1/treasury/alerts/{uuid}/resolve` |
| `webhook_deliveries.redispatch` | `POST /api/v1/webhook-deliveries/{uuid}/redispatch` |

**Dashboard route:** `POST /settings/api-integrations/payments-gateway/operations-console/bulk-action` (permission `payments_gateway.manage`)

Request body: `action`, `uuids[]`, optional `comments` (for alert actions).

**Safety rules**

- Bulk discard, resolve, and webhook redispatch require an explicit browser confirm dialog before submit.
- Each UUID is processed independently; partial success is reported (`Bulk action completed: X succeeded, Y failed.`) with failed UUIDs and gateway messages in the flash summary.
- Bulk actions are unavailable when the underlying list/action APIs return HTTP 404 (same rules as single-item quick actions).

**Incident investigation detail screens (Phase 7G — dashboard control plane)**

Operators can open dedicated investigation pages from incident panel **Investigate** links. Each page fetches a single record from payments.pradytecai.com — nothing is stored on the dashboard.

| Investigation route | Gateway read API |
|--------------------|------------------|
| `GET .../operations-console/dead-letters/{uuid}` | `GET /api/v1/operations/queue/dead-letters/{uuid}` |
| `GET .../operations-console/callback-logs/{uuid}` | `GET /api/v1/callback-logs/{uuid}` |
| `GET .../operations-console/webhook-deliveries/{uuid}` | `GET /api/v1/webhook-deliveries/{uuid}` |
| `GET .../operations-console/treasury-alerts/{uuid}` | `GET /api/v1/treasury/alerts/{uuid}` |

**Investigation workflow**

1. Open **Operations Console** and expand an incident panel.
2. Click **Investigate** on a row to review identity, tenant impact, failure reason, timestamps, and redacted payloads.
3. Follow related links (transaction, callback, webhook event/delivery) when the gateway exposes cross-references.
4. Choose remediation on the detail page (requires `payments_gateway.manage`):
   - Dead letter → replay if root cause fixed, discard if handled without replay
   - Failed callback → retry after verifying upstream state
   - Failed webhook delivery → redispatch after tenant endpoint is healthy
   - Treasury alert → acknowledge to signal ownership, resolve after remediation
5. Return to the Operations Console via **Back to Operations Console** and verify recovery in incident counts and activity stream.

Detail pages use collapsible JSON panels with pretty-printing, truncation for long payloads, optional copy, and explicit **Redacted** badges where the gateway masks sensitive fields.

If `GET /api/v1/treasury/alerts/{uuid}` returns HTTP 404, the page shows *"Alert detail API not available yet."* without storing alert data locally.

**Enriched investigation context (Phase 7I — dashboard control plane)**

Phase 7H detail APIs on payments.pradytecai.com return an `investigation` block alongside the primary record. The dashboard `IncidentInvestigationPresenter` consumes:

| Investigation field | Dashboard use |
|----------------------|---------------|
| `primary_record` | Incident identity summary |
| `tenant_impact` | Tenant/profile/PayBill impact panel |
| `related_records` | Related record table + navigation links |
| `recommended_next_actions` | Recommended next actions panel |
| `risk_level` | Risk badge (low / medium / high / critical) |

**Recommended action model**

Gateway action codes (e.g. `replay_dead_letter`, `acknowledge_alert`, `redispatch_webhook_event`) are mapped to dashboard capabilities:

| Mapping | Behavior |
|---------|----------|
| POST action available | Renders a remediation form when the operator has `payments_gateway.manage` |
| Navigate action | Renders an **Open** link when a related record URL exists |
| No dashboard capability | Shows **Unavailable in dashboard** badge (e.g. `resolve_unmatched_transaction`, audit trail review) |

**Related record navigation**

| Record type | Operations Console route |
|-------------|-------------------------|
| `dead_letter` | `.../operations-console/dead-letters/{uuid}` |
| `callback_log` | `.../operations-console/callback-logs/{uuid}` |
| `webhook_event` | `.../operations-console/webhook-events/{uuid}` |
| `webhook_delivery` | `.../operations-console/webhook-deliveries/{uuid}` |
| `unmatched_transaction` | `.../operations-console/unmatched-transactions/{uuid}` |
| `payment_transaction` | `.../transactions/{uuid}` |
| `treasury_alert` | `.../operations-console/treasury-alerts/{uuid}` |

**Operations summary APIs (Phase 7B — implemented on payments.pradytecai.com)**

- `GET /api/v1/operations/transactions/summary`
- `GET /api/v1/operations/callback-logs/summary`
- `GET /api/v1/operations/webhooks/summary`
- `GET /api/v1/operations/queue/overview`
- `GET /api/v1/operations/reconciliation/runs`
- `GET /api/v1/operations/reconciliation/unmatched`
- `GET /api/v1/operations/treasury-alerts`
- `GET /api/v1/operations/readiness/status`
