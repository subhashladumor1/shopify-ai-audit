# SHOPIFY SECURITY SPECIFICATION

**Package:** `subhash/shopify-ai-audit`  
**Namespace:** `Subhash\ShopifyAiAudit\Shopify\Security`  

---

## 1. Authentication & OAuth 2.0 Security

```mermaid
sequenceDiagram
    autonumber
    participant Merchant as Shopify Merchant / Browser
    participant Shopify as Shopify Admin
    participant App as Laravel App (Guardian Protected)

    Merchant->>App: GET /auth/shopify?shop=example.myshopify.com
    App->>App: Validate shop regex: ^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$
    App->>App: Generate random 256-bit state nonce & save in session/cache
    App-->>Merchant: Redirect to Shopify OAuth dialog with state, redirect_uri, scopes
    Merchant->>Shopify: Approve App Scopes
    Shopify-->>Merchant: Redirect to App callback with code, hmac, state, timestamp, shop
    Merchant->>App: GET /auth/shopify/callback?code=...&hmac=...&state=...
    App->>App: 1. Validate state equals session state (Replay/CSRF protection)
    App->>App: 2. Remove 'hmac' & 'signature', sort query keys lexicographically
    App->>App: 3. Compute hash_hmac('sha256', rawQuery, clientSecret)
    App->>App: 4. hash_equals(computedHmac, providedHmac) (Timing attack protection)
    App->>Shopify: POST /admin/oauth/access_token (code, client_id, client_secret)
    Shopify-->>App: { access_token, scope, associated_user }
    App->>App: Encrypt access_token with AES-256-GCM before DB write
```

### Critical Invariants
- **Domain Validation:** Every shop parameter MUST be validated against RFC-compliant myshopify subdomain regex before initiating any redirect.
- **Timing Attack Defense:** All HMAC and signature comparisons MUST use `hash_equals()`. Never use `===` or `==`.
- **State Validation:** The OAuth `state` parameter is mandatory and must expire within 10 minutes.

---

## 2. Webhook Signature & Payload Integrity

```mermaid
flowchart TD
    REQ["Incoming Webhook POST /api/webhooks/shopify"] --> CHECK_HEADER{"X-Shopify-Hmac-Sha256 header present?"}
    CHECK_HEADER -- No --> REJECT_401["Reject: HTTP 401 Unauthorized"]
    CHECK_HEADER -- Yes --> RAW_BODY["Read Raw Body: $request->getContent()"]
    RAW_BODY --> COMPUTE["Compute: hash_hmac('sha256', rawBody, webhookSecret, true)"]
    COMPUTE --> BASE64["Base64 Encode Result"]
    BASE64 --> COMPARE{"hash_equals(computed, header)?"}
    COMPARE -- No --> REJECT_403["Reject: HTTP 403 Forbidden (Tampered/Invalid)"]
    COMPARE -- Yes --> IDEMPOTENCY{"Check Webhook ID (X-Shopify-Webhook-Id) in Cache"}
    IDEMPOTENCY -- Duplicate --> ACK_200["Acknowledge HTTP 200 (Duplicate Ignored)"]
    IDEMPOTENCY -- New --> DISPATCH["Queue Job & Return HTTP 200 immediately"]
```

### Mandatory Compliance Webhooks
Under Shopify Partner Program and App Store Requirements, all public apps MUST handle mandatory privacy topics:
1. `customers/data_request` — Merchant or customer requests data held by the app.
2. `customers/redact` — Customer requests data erasure.
3. `shop/redact` — 48 hours after app uninstallation, merchant shop data must be erased.

The Guardian scanner verifies that routes and controller methods handling these topics exist, are HMAC-verified, and trigger appropriate asynchronous redaction/export jobs.

---

## 3. App Proxy Verification

Shopify App Proxies forward storefront requests to your app backend with a cryptographic signature query parameter.

Verification Algorithm:
1. Strip `signature` from the GET query array.
2. Sort parameters lexicographically by key.
3. Concatenate as key-value pairs (`key=value` or `key=val1,val2` for arrays).
4. Compute SHA-256 HMAC against the concatenated string using the client secret.
5. Verify via `hash_equals()` and check that `timestamp` is within an acceptable threshold (e.g., +/- 300 seconds) to prevent replay attacks.

---

## 4. Embedded App & Content Security Policy (CSP)

Embedded Shopify apps render inside an iframe in Shopify Admin.
- Traditional cookies fail due to browser third-party cookie restrictions.
- All embedded communication must use **Shopify App Bridge Session Tokens** (JWTs signed with app client secret).
- Responses must specify the CSP header:
  ```http
  Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com;
  ```
- Any X-Frame-Options header must NOT be set to `DENY` or `SAMEORIGIN`.

---

## 5. Theme & Extension Security

- **Liquid Escaping:** Untrusted user/merchant metafields and attributes rendered in Liquid templates must pass through `| escape` or `| json`.
- **ScriptTags Deprecation:** ScriptTag API is prohibited for new apps. Theme App Extensions with App Blocks and App Embeds must be used instead.
- **Shopify Functions & UI Extensions:** Network requests from UI extensions are restricted to declared hosts; customer data access in Checkout UI extensions must be bounded and declared in `shopify.extension.toml`.
