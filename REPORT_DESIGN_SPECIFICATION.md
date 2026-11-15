# REPORT DESIGN SPECIFICATION

**Package:** `subhash/shopify-ai-audit`  
**Namespace:** `Subhash\ShopifyAiAudit\Reports`  

---

## 1. Supported Export Formats
Guardian generates audit and compliance reports in 8 standardized formats:

1. **HTML:** Self-contained, responsive report suitable for client delivery and dashboard viewing.
2. **PDF:** Paginated, print-ready executive summary and technical deep dive.
3. **JSON:** Full structured dump of audit manifest, coverage, findings, and evidence.
4. **SARIF (v2.1.0):** OASIS Standard Static Analysis Results Interchange Format for GitHub Advanced Security / GitLab CI integration.
5. **CSV:** Tabular export of findings, severities, and line numbers for spreadsheet triage.
6. **XLSX:** Multi-sheet workbook (Summary, Critical Findings, API Deprecations, Governance).
7. **Markdown:** Clean GitHub Flavored Markdown suitable for PR comments and issue tracking.
8. **CycloneDX (JSON/XML):** Software Bill of Materials (SBOM) for dependencies and licenses.

---

## 2. Standard Report Structure
Every generated report includes:
- **Header:** Project Name, Audit Timestamp, Guardian Engine Version, Commit SHA, Git Branch.
- **Executive Summary:** Overall Risk Score, Total Findings by Severity, Pass/Fail Thresholds.
- **Coverage Ledger:** Scanned files, skipped files (with reasons), confidence metrics.
- **Domain Breakdown:**
  - Shopify Security & OAuth
  - Shopify API Deprecations & GraphQL Costs
  - App Store Review Readiness
  - Partner Program Governance Risk
  - Laravel & PHP Core Security
  - Frontend & Database Optimization
- **Detailed Findings:** Rule ID, Title, Severity, Verified Code Snippet, Root Cause, Remediation Steps.
- **Limitations & Disclaimers:** Explicit notice that static scanning does not replace dynamic penetration testing or guarantee official Shopify App Store approval.
