# CHANGELOG

All notable changes to `subhash/shopify-ai-audit` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0-dev] - 2026-11-15

### Added
- Initial project architecture and design specifications.
- Core Audit Engine: `AuditManager`, `AuditOrchestrator`, `ProjectDiscovery`, `ScannerRegistry`, `RuleRegistry`.
- Shopify Security Engine: OAuth verification, App Bridge Session Tokens, Webhook HMAC, App Proxy signature, GDPR mandatory webhooks.
- Shopify API Intelligence Engine: Admin GraphQL, Storefront API, Customer Account API (.well-known discovery), ShopifyQL, Payments Apps API, version lifecycle & deprecations.
- App Store Readiness Engine: Checklist, category modules, storefront performance impact weighting.
- Partner Governance Engine: Policies, enforcement risk score, app removal impact, voluntary sunset planner.
- Laravel & PHP Security Engine: SQLi, XSS, CSRF, SSRF, RCE, Path traversal, Mass assignment, secrets detection.
- Frontend, Database, Cloudflare, SEO & Performance Scanners.
- AI Platform & Laravel AI Guard integration: Multi-provider bridge, TokenGuard budgeting, fail-closed enforcement.
- Multi-Agent System with Coordinator and 26 specialist agents.
- 40 Core Skills, 20 Security Skills, 21 Shopify Subskills, and Skills Security Supervisor.
- Native Observability Engine: Telescope-style collectors for Requests, SQL queries, Shopify GraphQL calls, Exceptions, AI executions.
- Native Blade Dashboard: Dark/light responsive interface for audits, findings, telemetry, and remediation.
- Remediation Engine: Unified patch generation, diff preview, IDE prompt generation (Antigravity, Claude Code, Codex), verification runner.
- Multi-format Reporting Engine: HTML, PDF, JSON, SARIF 2.1.0, CSV, XLSX, Markdown, CycloneDX.
- Comprehensive Artisan CLI commands under `guardian:*`.
- Full REST API with authentication and resource transformers.
- PHPUnit test suite with Orchestra Testbench.
