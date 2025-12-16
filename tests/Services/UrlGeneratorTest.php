<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests\Services;

use Caseproof\AiSummarize\Services\UrlGenerator;
use GroundLevel\Testing\TestCase;

class UrlGeneratorTest extends TestCase
{
    private UrlGenerator $urlGenerator;

    public function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = new UrlGenerator();
    }

    public function testGenerateChatGptUrl(): void
    {
        $prompt = 'Summarize this article';
        $url    = $this->urlGenerator->generate('chatgpt', $prompt);

        $this->assertStringContainsString('chat.openai.com', $url);
        $this->assertStringContainsString(rawurlencode($prompt), $url);
    }

    public function testGeneratePerplexityUrl(): void
    {
        $prompt = 'Summarize this article';
        $url    = $this->urlGenerator->generate('perplexity', $prompt);

        $this->assertStringContainsString('perplexity.ai', $url);
        $this->assertStringContainsString(rawurlencode($prompt), $url);
    }

    public function testGenerateClaudeReturnsEmpty(): void
    {
        // Claude is a modal service (empty url_template), so generate should return empty.
        $prompt = 'Summarize this article';
        $url    = $this->urlGenerator->generate('claude', $prompt);

        $this->assertSame('', $url);
    }

    public function testGenerateCopilotUrl(): void
    {
        $prompt = 'Summarize this article';
        $url    = $this->urlGenerator->generate('copilot', $prompt);

        $this->assertStringContainsString('copilot.microsoft.com', $url);
        $this->assertStringContainsString(rawurlencode($prompt), $url);
    }

    public function testGenerateYouUrl(): void
    {
        $prompt = 'Summarize this article';
        $url    = $this->urlGenerator->generate('you', $prompt);

        $this->assertStringContainsString('you.com', $url);
        $this->assertStringContainsString(rawurlencode($prompt), $url);
        $this->assertStringContainsString('chatMode=default', $url);
    }

    public function testGenerateWithSpecialCharacters(): void
    {
        $prompt = 'Summarize: https://example.com/post?id=123&user=test';
        $url    = $this->urlGenerator->generate('chatgpt', $prompt);

        $this->assertStringContainsString(rawurlencode($prompt), $url);
        $this->assertStringNotContainsString('&user=test', $url); // Should be encoded.
    }

    public function testGenerateWithUnsupportedService(): void
    {
        $url = $this->urlGenerator->generate('invalid-service', 'test');

        $this->assertSame('', $url);
    }

    public function testGetServiceName(): void
    {
        $this->assertSame('ChatGPT', $this->urlGenerator->getServiceName('chatgpt'));
        $this->assertSame('Perplexity', $this->urlGenerator->getServiceName('perplexity'));
        $this->assertSame('Claude', $this->urlGenerator->getServiceName('claude'));
        $this->assertSame('Copilot', $this->urlGenerator->getServiceName('copilot'));
        $this->assertSame('You.com', $this->urlGenerator->getServiceName('you'));
    }

    public function testGetSupportedServices(): void
    {
        $services = $this->urlGenerator->getSupportedServices();

        $this->assertIsArray($services);
        $this->assertContains('chatgpt', $services);
        $this->assertContains('perplexity', $services);
        $this->assertContains('claude', $services);
        $this->assertContains('copilot', $services);
        $this->assertContains('you', $services);
        $this->assertCount(5, $services);
    }

    public function testIsServiceSupported(): void
    {
        $this->assertTrue($this->urlGenerator->isServiceSupported('chatgpt'));
        $this->assertTrue($this->urlGenerator->isServiceSupported('perplexity'));
        $this->assertTrue($this->urlGenerator->isServiceSupported('claude'));
        $this->assertTrue($this->urlGenerator->isServiceSupported('copilot'));
        $this->assertTrue($this->urlGenerator->isServiceSupported('you'));
        $this->assertFalse($this->urlGenerator->isServiceSupported('invalid-service'));
    }
}
