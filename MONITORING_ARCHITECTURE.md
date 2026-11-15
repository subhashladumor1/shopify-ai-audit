# OBSERVABILITY & MONITORING ARCHITECTURE

**Package:** `subhash/shopify-ai-audit`  
**Namespace:** `Subhash\ShopifyAiAudit\Monitoring`  

---

## 1. Telescope/Pulse-Style Architecture

Guardian includes an internal observability subsystem designed specifically for Shopify apps and Laravel backends. It functions seamlessly in synchronous environments without requiring Redis or queues, while supporting high-throughput batching when available.

```mermaid
flowchart TD
    subgraph INGRESS["Application Telemetry Hooks"]
        REQ["HTTP Requests Middleware"]
        SQL["DB::listen Event Listener"]
        API["Guzzle / Http Middleware (Shopify GraphQL)"]
        EXC["Log / Exception Handler Hook"]
        JOB["Queue::after & Queue::failing Listeners"]
        AI["AI Turn & Tool Telemetry"]
    end

    subgraph COLLECTORS["Collectors & Processors"]
        C_REQ["RequestCollector"]
        C_SQL["QueryCollector (N+1 Detector)"]
        C_API["ApiCallCollector (GraphQL Cost & Quota)"]
        C_EXC["ExceptionCollector"]
        C_AI["AiExecutionCollector"]
    end

    subgraph PIPELINE["Sanitization & Protection"]
        MASK["Sensitive Data & PII Redactor"]
        SAMPLE["Probabilistic Sampler (0.0 - 1.0)"]
        RECURSION["Recursion Barrier (Prevents monitoring its own queries)"]
    end

    subgraph STORAGE["Storage & Maintenance"]
        DB[("guardian_monitor_* Database Tables")]
        PRUNE["Scheduled Pruning Job (guardian:monitor:prune)"]
    end

    REQ --> C_REQ
    SQL --> C_SQL
    API --> C_API
    EXC --> C_EXC
    AI --> C_AI

    C_REQ --> MASK
    C_SQL --> MASK
    C_API --> MASK
    C_EXC --> MASK
    C_AI --> MASK

    MASK --> SAMPLE
    SAMPLE --> RECURSION
    RECURSION --> DB
    DB --> PRUNE
```

---

## 2. Specialized Shopify API Monitoring
For all outgoing calls to Shopify Admin GraphQL:
- Extracts `X-Shopify-Shop-Api-Call-Limit` headers (e.g. `32/40`).
- Extracts GraphQL query cost extensions:
  ```json
  "extensions": {
    "cost": {
      "requestedQueryCost": 120,
      "actualQueryCost": 85,
      "throttleStatus": {
        "maximumAvailable": 2000,
        "currentlyAvailable": 1915,
        "restoreRate": 100
      }
    }
  }
  ```
- Warns proactively when `currentlyAvailable < 200` to prevent rate-limit crashes.
