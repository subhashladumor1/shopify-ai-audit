# AI SKILLS SPECIFICATION

**Package:** `subhash/shopify-ai-audit`  
**Namespace:** `Subhash\ShopifyAiAudit\AI\Skills`  

---

## 1. Skill Folder & File Specification

Each skill is self-contained in `resources/audit-skills/<skill-name>/`:

```text
resources/audit-skills/<skill-name>/
├── skill.yaml              # Metadata, version, target frameworks, hashes
├── SKILL.md                # System instructions and operational guidelines
├── instructions.md         # Detailed detection & remediation heuristics
├── tools.yaml              # Associated tools permitted for this skill
├── rules/                  # Specific deterministic rule IDs mapped to this skill
├── examples/               # Vulnerable vs secure code examples
├── remediation/            # Canonical patch templates
└── evaluations/            # Test benchmarks for validating LLM adherence
```

---

## 2. Skill Inventory

### 2.1 40 Core Skill Areas
1. OWASP Web Security
2. Laravel Security
3. PHP Security
4. API Security
5. Cloudflare Security
6. Secrets Management
7. Authentication and Authorization
8. Laravel Architecture
9. Laravel Best Practices
10. Eloquent Optimization
11. Laravel API Development
12. Queues, Events and Jobs
13. Laravel Testing
14. React Best Practices
15. Next.js Best Practices
16. TypeScript Best Practices
17. Redux Toolkit
18. RTK Query
19. TanStack Query
20. Web Components
21. Shopify App Security
22. Shopify App Review
23. Shopify Admin API
24. Shopify GraphQL
25. Shopify Webhooks
26. Shopify App Bridge
27. Shopify Polaris
28. Shopify Theme Extensions
29. Shopify POS Extensions
30. Shopify Customer Accounts
31. MySQL Optimization
32. MongoDB Best Practices
33. Redis Optimization
34. Core Web Vitals
35. Lighthouse Performance
36. Technical SEO
37. Accessibility
38. Docker Security
39. CI/CD DevSecOps
40. AI Agent Engineering, Tool Calling & Eval

### 2.2 20 Security Skills
1. Cloudflare Security Audit
2. OWASP API Security
3. OWASP ASVS
4. OWASP Agentic Skills Top 10
5. AI Agent Security
6. Prompt Injection Defense
7. Tool Calling Security
8. AI Skill Supply Chain Security
9. Agent Isolation and Sandboxing
10. Agent Identity and Permissions
11. LLM Data Leakage Prevention
12. LLM Output Security
13. MCP Server Security
14. Webhook Protocol Security
15. SSRF and Network Security
16. Multi-Tenant Security
17. Dependency Supply Chain Security
18. Cloud and Container Security
19. Security Testing and Validation
20. Agent Governance and Auditability

### 2.3 21 Shopify Security Subskills
1. `shopify-app-security`
2. `shopify-oauth-security`
3. `shopify-session-token-security`
4. `shopify-access-token-security`
5. `shopify-ip-allowlisting`
6. `shopify-webhook-security`
7. `shopify-app-proxy-security`
8. `shopify-admin-api-security`
9. `shopify-graphql-security`
10. `shopify-protected-customer-data`
11. `shopify-privacy-compliance`
12. `shopify-embedded-app-security`
13. `shopify-theme-extension-security`
14. `shopify-customer-account-security`
15. `shopify-storefront-security`
16. `shopify-checkout-extension-security`
17. `shopify-pos-extension-security`
18. `shopify-functions-security`
19. `shopify-web-pixel-security`
20. `shopify-payments-security`
21. `shopify-partner-governance`

---

## 3. Skills Security Supervisor

The `SkillsSecuritySupervisor` validates any user-provided or custom skill before execution:
- Computes SHA-256 integrity hash of `skill.yaml` and `SKILL.md`.
- Inspects instructions for shell breakout patterns (`exec`, `rm -rf`, `curl | bash`, backticks).
- Blocks undeclared tool requests or elevated filesystem/network permissions.
