<?php

namespace Subhash\ShopifyAiAudit\Contracts;

interface AISkillInterface
{
    public function id(): string;

    public function name(): string;

    public function description(): string;

    public function version(): string;

    /**
     * @return array<int, string>
     */
    public function supportedRules(): array;

    public function instructions(): string;

    /**
     * @return array<int, string>
     */
    public function allowedTools(): array;
}
