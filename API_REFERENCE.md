# REST API REFERENCE

**Package:** `subhash/shopify-ai-audit`  
**Base Route Prefix:** `/api/guardian` (configurable in `config/shopify-ai-audit.php`)

---

## 1. Endpoints Overview

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/guardian/health` | Service health, version, scanner status | No / Configurable |
| `GET` | `/api/guardian/audits` | List historic audit runs (paginated) | Yes |
| `POST` | `/api/guardian/audits` | Trigger new audit (supports filter switches) | Yes |
| `GET` | `/api/guardian/audits/{id}` | Detailed audit results with coverage | Yes |
| `GET` | `/api/guardian/findings` | Filter findings by severity/status/category | Yes |
| `GET` | `/api/guardian/findings/{id}` | Finding details, evidence, and suggestions | Yes |
| `POST` | `/api/guardian/findings/{id}/remediate` | Generate unified diff patch | Yes |
| `POST` | `/api/guardian/findings/{id}/verify` | Verify if finding was resolved | Yes |
| `GET` | `/api/guardian/shopify/apis` | Discovered Shopify APIs and endpoints | Yes |
| `GET` | `/api/guardian/shopify/versions` | API version lifecycle & deprecations | Yes |
| `GET` | `/api/guardian/shopify/app-store` | App store readiness evaluation | Yes |
| `GET` | `/api/guardian/shopify/governance` | Partner policy risks and sunset plans | Yes |
| `GET` | `/api/guardian/monitor/requests` | Inbound HTTP request telemetry | Yes |
| `GET` | `/api/guardian/monitor/queries` | Monitored SQL queries & N+1 patterns | Yes |
| `GET` | `/api/guardian/monitor/apis` | Outbound Shopify API calls & quotas | Yes |
| `GET` | `/api/guardian/reports` | List generated reports | Yes |
| `POST` | `/api/guardian/reports` | Generate report in requested format | Yes |
| `GET` | `/api/guardian/reports/{id}/download` | Secure report download | Yes |

---

## 2. Sample Request & Response

### Trigger Audit (`POST /api/guardian/audits`)
**Request Payload:**
```json
{
  "mode": "read_only",
  "categories": ["shopify_security", "shopify_api", "laravel_security"],
  "min_severity": "medium",
  "include_ai_analysis": false
}
```

**Response Payload (HTTP 200 OK):**
```json
{
  "data": {
    "audit_id": "9d8b74c2-02cf-4b71-92ab-1a3b8c4598d1",
    "status": "completed",
    "risk_score": 18.5,
    "risk_level": "low",
    "summary": {
      "total_findings": 2,
      "critical": 0,
      "high": 1,
      "medium": 1,
      "low": 0
    },
    "coverage": {
      "scanned_files": 142,
      "total_rules_evaluated": 65,
      "percentage": 98.4
    },
    "created_at": "2026-11-15T08:00:00Z"
  }
}
```
