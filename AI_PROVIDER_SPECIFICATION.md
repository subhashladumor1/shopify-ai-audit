# AI PROVIDER SPECIFICATION

**Package:** `subhash/shopify-ai-audit`  
**Namespace:** `Subhash\ShopifyAiAudit\AI\Providers`  

---

## 1. Supported AI Providers & Model Matrix

| Provider ID | Adapter Class | Default Model | Capabilities Supported |
|---|---|---|---|
| `openai` | `OpenAIProvider` | `gpt-4o` | Tool Calling, Structured Outputs, JSON Mode, Streaming |
| `anthropic` | `AnthropicProvider` | `claude-3-5-sonnet-20241022` | Tool Calling, Streaming, Extended Thinking |
| `gemini` | `GeminiProvider` | `gemini-1.5-pro` | Tool Calling, Multimodal, Long Context (1M+ tokens) |
| `groq` | `GroqProvider` | `llama-3.3-70b-versatile` | Ultra-fast Tool Calling, JSON Mode |
| `deepseek` | `DeepSeekProvider` | `deepseek-chat` | Reasoning, Tool Calling, Code Synthesis |
| `mistral` | `MistralProvider` | `mistral-large-latest` | Tool Calling, Structured Outputs |
| `ollama` | `OllamaProvider` | `qwen2.5-coder:14b` | Local/Offline Execution, Privacy-First Scanning |
| `custom` | `CustomOpenAICompatibleProvider` | Configurable | Any OpenAI-compatible proxy or vLLM instance |

---

## 2. Provider Contract Interface

```php
namespace Subhash\ShopifyAiAudit\Contracts;

use Subhash\ShopifyAiAudit\DTOs\AIRequest;
use Subhash\ShopifyAiAudit\DTOs\AIResponse;
use Subhash\ShopifyAiAudit\DTOs\ProviderCapabilities;

interface AIProviderInterface
{
    public function id(): string;

    public function name(): string;

    public function capabilities(string $model): ProviderCapabilities;

    public function isAvailable(): bool;

    public function execute(AIRequest $request): AIResponse;
}
```

---

## 3. Dynamic Capability Detection
Before routing a prompt requiring function calling or structured JSON schemas, the orchestrator queries `capabilities($model)`:
- If `supportsToolCalling` is false, fallback to system-prompt schema instructions.
- If `contextWindow` is smaller than estimated prompt tokens, chunk input files or summarize context before transmission.
