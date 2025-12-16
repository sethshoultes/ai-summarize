<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests;

use Caseproof\AiSummarize\Bootstrap;
use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Services\UrlGenerator;
use Caseproof\AiSummarize\Blocks\AiSummarizeButtons;
use Caseproof\AiSummarize\GroundLevel\Package\PluginConfig;
use GroundLevel\Testing\WPTestCase;

/**
 * Unit tests for the Bootstrap class.
 *
 * @coversDefaultClass \Caseproof\AiSummarize\Bootstrap
 */
class BootstrapTest extends WPTestCase
{
    private Bootstrap $bootstrap;
    private string $mainFile;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Unregister block if already registered from previous tests.
        $registry = \WP_Block_Type_Registry::get_instance();
        if ($registry->is_registered('ai-summarize/buttons')) {
            $registry->unregister('ai-summarize/buttons');
        }

        // Create a mock main file path.
        $this->mainFile = dirname(__DIR__) . '/ai-summarize.php';

        // Create config and bootstrap instance.
        $config          = new PluginConfig($this->mainFile, ['db_prefix' => 'ai_summarize']);
        $this->bootstrap = new Bootstrap($config);
    }

    /**
     * Tear down test environment.
     */
    protected function tearDown(): void
    {
        // Unregister block after test.
        $registry = \WP_Block_Type_Registry::get_instance();
        if ($registry->is_registered('ai-summarize/buttons')) {
            $registry->unregister('ai-summarize/buttons');
        }

        parent::tearDown();
    }

    /**
     * Test bootstrap initialization.
     *
     * @covers ::init
     */
    public function testInit(): void
    {
        // Initialize the bootstrap.
        $this->bootstrap->init();

        // Verify container has required services.
        $container = $this->bootstrap->container();

        $this->assertTrue($container->has('settings'));
        $this->assertTrue($container->has('urlGenerator'));
        $this->assertTrue($container->has('aiSummarizeButtons'));

        // Verify services are correct types.
        $this->assertInstanceOf(Settings::class, $container->get('settings'));
        $this->assertInstanceOf(UrlGenerator::class, $container->get('urlGenerator'));
        $this->assertInstanceOf(AiSummarizeButtons::class, $container->get('aiSummarizeButtons'));
    }

    /**
     * Test that database connection service is registered.
     *
     * @covers ::init
     */
    public function testDatabaseConnectionRegistered(): void
    {
        global $wpdb;

        $this->bootstrap->init();
        $container = $this->bootstrap->container();

        // Import the constant to get the actual key.
        $dbKey = \Caseproof\AiSummarize\GroundLevel\Database\Service::DB_CONNECTION;

        // Database connection should be available under the correct key.
        $this->assertTrue($container->has($dbKey));
        $this->assertSame($wpdb, $container->get($dbKey));
    }

    /**
     * Test hook configuration.
     *
     * @covers ::configureHooks
     */
    public function testConfigureHooks(): void
    {
        // Initialize bootstrap to register hooks.
        $this->bootstrap->init();

        // Use reflection to access protected method.
        $reflection = new \ReflectionClass($this->bootstrap);
        $method     = $reflection->getMethod('configureHooks');
        $method->setAccessible(true);

        $hooks = $method->invoke($this->bootstrap);

        // Verify we have the expected number of hooks.
        $this->assertIsArray($hooks);
        $this->assertCount(3, $hooks);

        // Verify hook types.
        foreach ($hooks as $hook) {
            $this->assertInstanceOf(
                'Caseproof\AiSummarize\GroundLevel\Support\Models\Hook',
                $hook
            );
        }
    }

    /**
     * Test that admin menu hook is registered.
     *
     * @covers ::configureHooks
     */
    public function testAdminMenuHookRegistered(): void
    {
        $this->bootstrap->init();

        // Verify admin_menu action is registered.
        $this->assertNotFalse(
            has_action('admin_menu', ['Caseproof\AiSummarize\Admin\Page', 'register'])
        );
    }

    /**
     * Test that init hook is configured to register block.
     *
     * @covers ::configureHooks
     */
    public function testInitHookConfigured(): void
    {
        $this->bootstrap->init();

        // Verify init hook is registered with a callback.
        $this->assertGreaterThan(0, has_action('init'));

        // Get the block registry.
        $registry = \WP_Block_Type_Registry::get_instance();

        // Verify block gets registered when we call the service directly.
        $container = $this->bootstrap->container();
        $block     = $container->get('aiSummarizeButtons');
        $block->register();

        // Verify block is now registered.
        $this->assertTrue($registry->is_registered('ai-summarize/buttons'));
    }

    /**
     * Test that wp_enqueue_scripts hook callback works.
     *
     * @covers ::configureHooks
     */
    public function testEnqueueScriptsHookCallback(): void
    {
        $this->bootstrap->init();

        // Trigger the wp_enqueue_scripts action.
        do_action('wp_enqueue_scripts');

        // Verify the CSS is enqueued.
        $this->assertTrue(wp_style_is('ai-summarize-blocks', 'enqueued'));
    }

    /**
     * Test container access from bootstrap.
     *
     * @covers ::init
     */
    public function testContainerAccess(): void
    {
        $this->bootstrap->init();

        $container = $this->bootstrap->container();

        // Verify container is accessible.
        $this->assertNotNull($container);

        // Verify we can get services from container.
        $settings     = $container->get('settings');
        $urlGenerator = $container->get('urlGenerator');
        $block        = $container->get('aiSummarizeButtons');

        $this->assertInstanceOf(Settings::class, $settings);
        $this->assertInstanceOf(UrlGenerator::class, $urlGenerator);
        $this->assertInstanceOf(AiSummarizeButtons::class, $block);
    }

    /**
     * Test that services are singletons.
     *
     * @covers ::init
     */
    public function testServicesAreSingletons(): void
    {
        $this->bootstrap->init();
        $container = $this->bootstrap->container();

        // Get services twice.
        $settings1 = $container->get('settings');
        $settings2 = $container->get('settings');

        $urlGenerator1 = $container->get('urlGenerator');
        $urlGenerator2 = $container->get('urlGenerator');

        // Verify same instance is returned.
        $this->assertSame($settings1, $settings2);
        $this->assertSame($urlGenerator1, $urlGenerator2);
    }
}
