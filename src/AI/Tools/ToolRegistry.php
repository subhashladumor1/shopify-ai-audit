<?php

namespace Subhash\ShopifyAiAudit\AI\Tools;

use Subhash\ShopifyAiAudit\Shopify\API\ApiRegistry;

class ToolRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $tools = [];

    public function __construct()
    {
        $this->bootDefaultTools();
    }

    protected function bootDefaultTools(): void
    {
        $this->register([
            'id' => 'project.search_code',
            'description' => 'Searches for regex or string pattern across codebase files.',
            'is_read_only' => true,
            'requires_approval' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search pattern or keyword'],
                    'extension' => ['type' => 'string', 'description' => 'File extension filter (e.g. php, liquid)'],
                ],
                'required' => ['query'],
            ],
            'handler' => function (array $args, string $basePath) {
                $query = $args['query'] ?? '';
                $matches = [];

                // Search implementation
                return ['matches' => $matches, 'query' => $query];
            },
        ]);

        $this->register([
            'id' => 'shopify.check_api_version',
            'description' => 'Checks whether a Shopify API quarterly version is supported or deprecated.',
            'is_read_only' => true,
            'requires_approval' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'version' => ['type' => 'string', 'description' => 'Quarterly version string (e.g. 2025-01)'],
                ],
                'required' => ['version'],
            ],
            'handler' => function (array $args) {
                $registry = new ApiRegistry;

                return $registry->checkVersion($args['version'] ?? 'unstable');
            },
        ]);

        $this->register([
            'id' => 'code.generate_patch',
            'description' => 'Generates a unified diff patch to remediate a finding.',
            'is_read_only' => false,
            'requires_approval' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'finding_id' => ['type' => 'string', 'description' => 'Unique ID of the finding'],
                    'patch_diff' => ['type' => 'string', 'description' => 'Unified diff string'],
                ],
                'required' => ['finding_id', 'patch_diff'],
            ],
            'handler' => function (array $args) {
                return ['status' => 'proposal_created', 'requires_user_approval' => true];
            },
        ]);
    }

    /**
     * @param  array<string, mixed>  $tool
     */
    public function register(array $tool): self
    {
        $this->tools[$tool['id']] = $tool;

        return $this;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->tools;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $id): ?array
    {
        return $this->tools[$id] ?? null;
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function execute(string $toolId, array $args, string $basePath): array
    {
        $tool = $this->get($toolId);
        if (! $tool) {
            throw new \InvalidArgumentException("Tool {$toolId} is not registered.");
        }

        if (! empty($tool['requires_approval'])) {
            return [
                'status' => 'PENDING_APPROVAL',
                'tool_id' => $toolId,
                'message' => 'Action requires explicit user confirmation before applying.',
            ];
        }

        $handler = $tool['handler'];

        return $handler($args, $basePath);
    }
}
