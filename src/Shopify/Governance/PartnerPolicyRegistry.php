<?php

namespace Subhash\ShopifyAiAudit\Shopify\Governance;

class PartnerPolicyRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $policies = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $enforcementActions = [];

    public function __construct()
    {
        $this->bootPolicies();
        $this->bootEnforcementActions();
    }

    protected function bootPolicies(): void
    {
        $this->policies = [
            'partner_program_agreement' => [
                'id' => 'partner_program_agreement',
                'title' => 'Shopify Partner Program Agreement',
                'url' => 'https://www.shopify.com/partners/terms',
                'key_clauses' => [
                    'Section 2.2: Ethical conduct and honest representation',
                    'Section 3.1: Revenue share and billing obligations',
                    'Section 8.3: Data protection and merchant privacy',
                ],
            ],
            'api_terms' => [
                'id' => 'api_terms',
                'title' => 'Shopify API License and Terms of Use',
                'url' => 'https://www.shopify.com/legal/api-terms',
                'key_clauses' => [
                    'Section 2.1: Prohibited benchmarking or competing products',
                    'Section 2.3: Excessive rate-limit consumption',
                    'Section 3.2: Protection of merchant and customer data',
                ],
            ],
            'acceptable_use_policy' => [
                'id' => 'acceptable_use_policy',
                'title' => 'Shopify Acceptable Use Policy (AUP)',
                'url' => 'https://www.shopify.com/legal/aup',
                'key_clauses' => [
                    'Malicious or deceptive software prohibited',
                    'Phishing, spam, and unsolicited communications prohibited',
                    'Interference with Shopify platform infrastructure prohibited',
                ],
            ],
            'review_policy' => [
                'id' => 'review_policy',
                'title' => 'Shopify App Store Review Policies',
                'url' => 'https://help.shopify.com/en/partners/help-support/faq/reviews',
                'key_clauses' => [
                    'Incentivizing positive reviews is strictly prohibited',
                    'Threatening merchants or withholding support for reviews is prohibited',
                    'Coerced or artificial review generation leads to delisting',
                ],
            ],
            'app_removal_policy' => [
                'id' => 'app_removal_policy',
                'title' => 'Shopify App Removal and Deprecation Guidelines',
                'url' => 'https://help.shopify.com/en/partners/help-support/faq/removal',
                'key_clauses' => [
                    'Removal triggers: Security vulnerabilities, billing non-compliance, abandoned apps',
                    'Merchant continuity: Apps uninstalled automatically in critical cases',
                    'Deprecation of unpublished apps without activity',
                ],
            ],
        ];
    }

    protected function bootEnforcementActions(): void
    {
        $this->enforcementActions = [
            'review_removal' => [
                'action' => 'Review Removal / Lock',
                'severity' => 'moderate',
                'impact' => 'Reviews suspected of manipulation are purged and merchant review capability frozen.',
            ],
            'promotional_delisting' => [
                'action' => 'Promotional Delisting',
                'severity' => 'moderate',
                'impact' => 'App removed from "Built for Shopify", search recommendations, and editorial collections.',
            ],
            'install_freeze' => [
                'action' => 'New Installation Freeze',
                'severity' => 'high',
                'impact' => 'Existing merchants retain access, but no new merchants can discover or install the app.',
            ],
            'app_store_delisting' => [
                'action' => 'App Store Delisting',
                'severity' => 'high',
                'impact' => 'App listing removed from public search; unpublished status enforced.',
            ],
            'merchant_app_removal' => [
                'action' => 'Automated Merchant App Removal',
                'severity' => 'critical',
                'impact' => 'Shopify forcibly uninstalls the app from all active merchant stores and revokes tokens.',
            ],
            'api_revocation' => [
                'action' => 'API Access Revocation',
                'severity' => 'critical',
                'impact' => 'Client ID and Secret blocked; all Admin and Storefront API requests immediately return 401.',
            ],
            'partner_account_termination' => [
                'action' => 'Partner Account Termination',
                'severity' => 'critical',
                'impact' => 'Partner dashboard deactivated, revenue share forfeited, all partner apps permanently purged.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function policies(): array
    {
        return $this->policies;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function enforcementActions(): array
    {
        return $this->enforcementActions;
    }
}
