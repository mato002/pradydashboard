# Acceptance checks — product app + Prady Dashboard

Run from a shell with access to product `.env` values and dashboard URL.

## E1 — License check (signed)

```bash
export PRADY_DASHBOARD_URL="https://dashboard.pradytecai.com"
export PRADY_PROJECT_API_TOKEN="your-project-token"
export PRADY_TENANT_KEY="your-tenant-key"
export PRADY_PRODUCT_KEY="property"
export PRADY_LICENSE_SECRET="your-license-secret"
export TENANT_DOMAIN="your-tenant-domain.com"

BODY="{\"tenant_key\":\"$PRADY_TENANT_KEY\",\"product_key\":\"$PRADY_PRODUCT_KEY\",\"domain\":\"$TENANT_DOMAIN\"}"
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "$PRADY_LICENSE_SECRET" | awk '{print $2}')

curl -sS -X POST "$PRADY_DASHBOARD_URL/api/v1/license/check" \
  -H "Authorization: Bearer $PRADY_PROJECT_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Prady-Signature: $SIG" \
  -d "$BODY"
```

**Pass:** HTTP 200, JSON includes `"allowed": true` (for active tenant).

## E2 — License check (missing signature)

Same as E1 but omit `X-Prady-Signature` header.

**Pass:** HTTP 401 when tenant has `license_secret` set.

## E3 — System info (authorized)

```bash
export PRADY_DASHBOARD_API_TOKEN="your-dashboard-api-token"
export TENANT_DOMAIN="https://your-tenant-domain.com"

curl -sS "$TENANT_DOMAIN/api/system/info" \
  -H "Authorization: Bearer $PRADY_DASHBOARD_API_TOKEN"
```

**Pass:** HTTP 200, JSON includes `"status"` and `"version"`.

## E4 — System info (unauthorized)

```bash
curl -sS -o /dev/null -w "%{http_code}" "$TENANT_DOMAIN/api/system/info"
```

**Pass:** HTTP 401.

## E5 — Product web UI

Open `https://your-tenant-domain.com` in a browser after middleware is wired.

**Pass:** Application loads (not 503 license-unavailable).

## E6 — Dashboard integration test

Prady Dashboard → Tenant → Integrations → Tenant system API → **Test connection**.

**Pass:** Status active / connection success.

## E7 — License logs

Prady Dashboard → License Logs → filter by `tenant_key` or domain.

**Pass:** Recent successful checks after E5.

## E8 — Read-only enforcement (manual)

On dashboard, restrict tenant access → reload product app → attempt a form POST.

**Pass:** POST returns 403 JSON or blocked; GET pages still load if policy allows.

## E9 — Payments webhook test (optional)

From dashboard Payments Gateway → Treasury Mapping → **Test endpoint** on webhook row.

**Pass:** Gateway reports delivery success; product returns HTTP 2xx.
