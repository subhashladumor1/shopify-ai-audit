# TESTING STRATEGY

**Package:** `subhash/shopify-ai-audit`  
**Test Framework:** PHPUnit 10/11 with Orchestra Testbench 9/10  

---

## 1. Test Architecture & Directory Structure

```text
tests/
├── TestCase.php               # Base testcase configuring Laravel package environment
├── Unit/                      # Fast in-memory unit tests (isolated classes)
│   ├── Core/                  # Discovery, Context, RiskScorer, Normalizer
│   ├── Shopify/               # OAuth, Webhook HMAC, App Proxy, Token verification
│   ├── Scanners/              # Rule checks against synthetically crafted AST/snippets
│   └── AI/                    # TokenGuard, Schema validation, Model capabilities
├── Feature/                   # Integration tests using SQLite in-memory database
│   ├── AuditPipelineTest.php  # End-to-end audit execution
│   ├── ShopifyApiTest.php     # Version matrix & deprecation checks
│   ├── MonitoringTest.php     # Request & Query collector hooks
│   ├── RemediationTest.php    # Patch creation and verification workflow
│   ├── ConsoleCommandsTest.php# Artisan guardian:* command executions
│   └── DashboardTest.php      # Blade dashboard view rendering & authorization
├── Security/                  # Security boundary & injection defense tests
│   ├── PromptInjectionTest.php# Untrusted file delimiter isolation
│   ├── SecretRedactionTest.php# Token scrubbing before persistence
│   └── PathTraversalTest.php  # Traversal attacks via custom file paths
└── Fixtures/                  # Realistic codebases (Vulnerable & Compliant)
    ├── vulnerable-shopify-app/
    ├── compliant-shopify-app/
    └── sample-liquid-extension/
```

---

## 2. Test Execution Commands

```bash
# Run all unit, feature, and security tests
vendor/bin/phpunit

# Run unit tests only
vendor/bin/phpunit --testsuite Unit

# Run security boundary tests
vendor/bin/phpunit --testsuite Security

# Run PHPStan static analysis
vendor/bin/phpstan analyse

# Check code formatting with Laravel Pint
vendor/bin/pint --test
```
