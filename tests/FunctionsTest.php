<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests;

use Caseproof\AiSummarize\Bootstrap;
use GroundLevel\Testing\WPTestCase;

use function Caseproof\AiSummarize\aiSummarizeApp;

/**
 * Unit tests for the plugin functions.
 *
 * @coversDefaultClass \Caseproof\AiSummarize\aiSummarizeApp
 */
class FunctionsTest extends WPTestCase
{
    private string $mainFile;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mainFile = dirname(__DIR__) . '/ai-summarize.php';
    }

    /**
     * Test aiSummarizeApp function returns Bootstrap instance.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAiSummarizeAppReturnsBootstrap(): void
    {
        $app = aiSummarizeApp($this->mainFile);

        $this->assertInstanceOf(Bootstrap::class, $app);
    }

    /**
     * Test aiSummarizeApp function returns singleton.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAiSummarizeAppReturnsSingleton(): void
    {
        $app1 = aiSummarizeApp($this->mainFile);
        $app2 = aiSummarizeApp($this->mainFile);

        // Should return the same instance.
        $this->assertSame($app1, $app2);
    }

    /**
     * Test aiSummarizeApp initializes container.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAiSummarizeAppInitializesContainer(): void
    {
        $app = aiSummarizeApp($this->mainFile);

        // Initialize the app.
        $app->init();

        // Verify container is initialized.
        $container = $app->container();
        $this->assertNotNull($container);

        // Verify BASE_PATH is set.
        $this->assertTrue($container->has('BASE_PATH'));
        $this->assertIsString($container->get('BASE_PATH'));
    }

    /**
     * Test aiSummarizeApp creates correct config.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAiSummarizeAppCreatesConfig(): void
    {
        $app = aiSummarizeApp($this->mainFile);
        $app->init();

        $container = $app->container();

        // Import the constant to get the actual key.
        $prefixKey = \Caseproof\AiSummarize\GroundLevel\Database\Service::PREFIX;

        // Verify database prefix is configured.
        $this->assertTrue($container->has($prefixKey));
        $this->assertEquals('ai_summarize', $container->get($prefixKey));
    }

    /**
     * Test that app can access config values.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAppCanAccessConfigValues(): void
    {
        $app = aiSummarizeApp($this->mainFile);
        $app->init();

        $config = $app->config();

        // Verify config methods work.
        $this->assertNotEmpty($config->getBasePath());
        $this->assertNotEmpty($config->getBaseUrl());
        $this->assertNotEmpty($config->getVersion());
    }

    /**
     * Test that app container has all required services.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAppContainerHasRequiredServices(): void
    {
        $app = aiSummarizeApp($this->mainFile);
        $app->init();

        $container = $app->container();

        // Import the constant to get the actual key.
        $dbKey = \Caseproof\AiSummarize\GroundLevel\Database\Service::DB_CONNECTION;

        // Verify all required services are registered.
        $this->assertTrue($container->has('settings'), 'Settings service not found');
        $this->assertTrue($container->has('urlGenerator'), 'UrlGenerator service not found');
        $this->assertTrue($container->has('aiSummarizeButtons'), 'AiSummarizeButtons service not found');
        $this->assertTrue($container->has($dbKey), 'Database connection not found');
    }

    /**
     * Test that app services are properly wired together.
     *
     * @covers ::aiSummarizeApp
     */
    public function testAppServicesAreProperlyWired(): void
    {
        $app = aiSummarizeApp($this->mainFile);
        $app->init();

        $container = $app->container();

        // Get the block service.
        $block = $container->get('aiSummarizeButtons');

        // Verify block has dependencies injected.
        $reflection       = new \ReflectionClass($block);
        $settingsProperty = $reflection->getProperty('settings');
        $settingsProperty->setAccessible(true);
        $settings = $settingsProperty->getValue($block);

        $urlGeneratorProperty = $reflection->getProperty('urlGenerator');
        $urlGeneratorProperty->setAccessible(true);
        $urlGenerator = $urlGeneratorProperty->getValue($block);

        // Verify dependencies are correct types.
        $this->assertInstanceOf('Caseproof\AiSummarize\Services\Settings', $settings);
        $this->assertInstanceOf('Caseproof\AiSummarize\Services\UrlGenerator', $urlGenerator);
    }
}
