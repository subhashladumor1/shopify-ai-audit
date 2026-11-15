# CONTRIBUTING TO SHOPIFY AI AUDIT

Thank you for contributing to `subhash/shopify-ai-audit`! This project aims to elevate the security, compliance, and developer experience across the entire Shopify and Laravel ecosystems.

---

## 1. Code of Conduct
We are committed to providing a welcoming, harassment-free experience for everyone. Respectful collaboration and constructive feedback are mandatory.

---

## 2. Development Setup

1. Fork and clone the repository:
   ```bash
   git clone https://github.com/subhashladumor1/shopify-ai-audit.git
   cd shopify-ai-audit
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run tests:
   ```bash
   vendor/bin/phpunit
   ```
4. Run static analysis:
   ```bash
   vendor/bin/phpstan analyse
   ```

---

## 3. Pull Request Guidelines

- **Adhere to PSR-12:** Run `vendor/bin/pint` prior to submitting.
- **Include Tests:** Any new rule, scanner, or feature must have corresponding unit or feature tests with fixtures.
- **Document Rules:** If adding a new audit rule, add it to `AUDIT_RULE_CATALOG.md` with official Shopify documentation references.
- **Zero Regressions:** All existing tests must pass.
