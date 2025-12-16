<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests\Admin;

use Caseproof\AiSummarize\Admin\Page;
use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Tests\Framework\TestCase;
use Caseproof\AiSummarize\GroundLevel\Container\Container;

/**
 * Unit tests for the Admin Page class.
 *
 * @coversDefaultClass \Caseproof\AiSummarize\Admin\Page
 */
class PageTest extends TestCase
{
    /**
     * Container instance for testing.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Settings service instance.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up container with settings service.
        $this->container = new Container();
        $this->settings  = new Settings();
        $this->container->addService('settings', function () {
            return $this->settings;
        });

        // Set container on Page class.
        Page::setContainer($this->container);

        // Clean up any existing options.
        delete_option(Settings::OPTION_KEY);
        delete_option(Settings::CATEGORY_OVERRIDES_KEY);
        delete_option(Settings::VERSION_KEY);
    }

    /**
     * Tear down test environment.
     */
    protected function tearDown(): void
    {
        // Clean up test data.
        delete_option(Settings::OPTION_KEY);
        delete_option(Settings::CATEGORY_OVERRIDES_KEY);
        delete_option(Settings::VERSION_KEY);

        // Clear settings errors between tests.
        global $wp_settings_errors;
        $wp_settings_errors = [];

        parent::tearDown();
    }

    /**
     * Test page constants and basic functionality.
     *
     * @covers ::CAPABILITY
     * @covers ::SLUG
     * @covers ::pageTitle
     */
    public function testPageConstants(): void
    {
        $this->assertEquals('manage_options', Page::CAPABILITY);
        $this->assertEquals('ai-summarize', Page::SLUG);
        $this->assertEquals('AI Summarize Settings', Page::pageTitle());
    }

    /**
     * Test current tab detection.
     *
     * @covers ::getCurrentTab
     */
    public function testGetCurrentTab(): void
    {
        // Test default tab.
        $_GET = [];
        $this->assertEquals('general', Page::getCurrentTab());

        // Test specific tab.
        $_GET['tab'] = 'services';
        $this->assertEquals('services', Page::getCurrentTab());

        // Test sanitization of invalid tab.
        $_GET['tab'] = '<script>alert("xss")</script>categories';
        $tab         = Page::getCurrentTab();
        $this->assertStringNotContainsString('<script>', $tab);
        $this->assertStringContainsString('categories', $tab);

        // Clean up.
        $_GET = [];
    }

    /**
     * Test settings sanitization callback.
     *
     * @covers ::sanitizeSettings
     */
    public function testSanitizeSettings(): void
    {
        $rawInput = [
            'global_prompt'    => '<script>bad</script>Good prompt',
            'enabled_services' => [
                'chatgpt'    => '1',
                'perplexity' => '',
                'claude'     => 'yes',
            ],
            'display_options'  => [
                'button_size' => 'large',
                'show_labels' => 'on',
                'layout'      => 'vertical',
            ],
        ];

        $sanitized = Page::sanitizeSettings($rawInput);

        $this->assertIsArray($sanitized);
        $this->assertArrayHasKey('global_prompt', $sanitized);
        $this->assertArrayHasKey('enabled_services', $sanitized);
        $this->assertArrayHasKey('display_options', $sanitized);

        // Verify HTML was sanitized.
        $this->assertStringNotContainsString('<script>', $sanitized['global_prompt']);
        $this->assertStringContainsString('Good prompt', $sanitized['global_prompt']);
    }

    /**
     * Test category override add functionality.
     *
     * @covers ::handleAddCategoryOverride
     */
    public function testHandleAddCategoryOverride(): void
    {
        // Create a test category.
        $categoryId = $this->factory()->category->create([
            'name' => 'Test Category',
        ]);

        // Set up POST data.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'add_category_override',
            'category_id'         => $categoryId,
            'prompt_template'     => 'Test prompt for category',
            'enabled_services'    => ['chatgpt', 'claude'],
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleAddCategoryOverride();

        // Verify override was added.
        $overrides   = $this->settings->getCategoryOverrides();
        $categoryKey = 'category_' . $categoryId;

        $this->assertArrayHasKey($categoryKey, $overrides);
        $this->assertEquals('Test prompt for category', $overrides[$categoryKey]['prompt_template']);
        $this->assertArrayHasKey('chatgpt', $overrides[$categoryKey]['enabled_services']);
        $this->assertArrayHasKey('claude', $overrides[$categoryKey]['enabled_services']);
        $this->assertTrue($overrides[$categoryKey]['enabled_services']['chatgpt']);
        $this->assertTrue($overrides[$categoryKey]['enabled_services']['claude']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test category override update functionality.
     *
     * @covers ::handleUpdateCategoryOverride
     */
    public function testHandleUpdateCategoryOverride(): void
    {
        // Create a test category.
        $categoryId = $this->factory()->category->create([
            'name' => 'Test Category',
        ]);

        // Set up existing override.
        $existingOverrides = [
            'category_' . $categoryId => [
                'prompt_template'  => 'Original prompt',
                'enabled_services' => [
                    'chatgpt' => true,
                ],
            ],
        ];
        $this->settings->updateCategoryOverrides($existingOverrides);

        // Set up POST data for update.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'update_category_override',
            'category_id'         => $categoryId,
            'prompt_template'     => 'Updated prompt for category',
            'enabled_services'    => ['perplexity', 'you'],
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleUpdateCategoryOverride();

        // Verify override was updated.
        $overrides   = $this->settings->getCategoryOverrides();
        $categoryKey = 'category_' . $categoryId;

        $this->assertArrayHasKey($categoryKey, $overrides);
        $this->assertEquals('Updated prompt for category', $overrides[$categoryKey]['prompt_template']);
        $this->assertArrayHasKey('perplexity', $overrides[$categoryKey]['enabled_services']);
        $this->assertArrayHasKey('you', $overrides[$categoryKey]['enabled_services']);
        $this->assertTrue($overrides[$categoryKey]['enabled_services']['perplexity']);
        $this->assertTrue($overrides[$categoryKey]['enabled_services']['you']);

        // Verify old services are not present.
        $this->assertArrayNotHasKey('chatgpt', $overrides[$categoryKey]['enabled_services']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test category override delete functionality.
     *
     * @covers ::handleDeleteCategoryOverride
     */
    public function testHandleDeleteCategoryOverride(): void
    {
        // Create a test category.
        $categoryId = $this->factory()->category->create([
            'name' => 'Test Category',
        ]);

        // Set up existing override.
        $existingOverrides = [
            'category_' . $categoryId => [
                'prompt_template' => 'Test prompt',
            ],
        ];
        $this->settings->updateCategoryOverrides($existingOverrides);

        // Verify override exists.
        $overrides = $this->settings->getCategoryOverrides();
        $this->assertArrayHasKey('category_' . $categoryId, $overrides);

        // Set up POST data for deletion.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'delete_category_override',
            'category_id'         => $categoryId,
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleDeleteCategoryOverride();

        // Verify override was deleted.
        $overrides = $this->settings->getCategoryOverrides();
        $this->assertArrayNotHasKey('category_' . $categoryId, $overrides);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test security: operations should fail without proper nonce.
     *
     * @covers ::handleFormSubmissions
     */
    public function testSecurityNonceRequired(): void
    {
        // Set up POST data without valid nonce.
        $_POST = [
            'action'      => 'add_category_override',
            'category_id' => 1,
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Attempt to handle form submission should fail.
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Security check failed.');

        // Use reflection to call private method.
        $reflection = new \ReflectionClass(Page::class);
        $method     = $reflection->getMethod('handleFormSubmissions');
        $method->setAccessible(true);
        $method->invoke(null);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test security: operations should fail without proper capability.
     *
     * @covers ::handleFormSubmissions
     */
    public function testSecurityCapabilityRequired(): void
    {
        // Mock current user without proper capability first.
        $userId = $this->factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);

        // Set up POST data with valid nonce (created after setting user).
        $nonce = wp_create_nonce('ai_summarize_settings');
        $_POST = [
            '_ai_summarize_nonce' => $nonce,
            'action'              => 'add_category_override',
            'category_id'         => 1,
        ];

        // Attempt to handle form submission should fail.
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('You do not have permission to perform this action.');

        // Use reflection to call private method.
        $reflection = new \ReflectionClass(Page::class);
        $method     = $reflection->getMethod('handleFormSubmissions');
        $method->setAccessible(true);
        $method->invoke(null);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test register method adds admin menu page.
     *
     * @covers ::register
     */
    public function testRegister(): void
    {
        // Register the page.
        $hookSuffix = Page::register();

        // Verify hook suffix is returned.
        $this->assertIsString($hookSuffix);
        $this->assertNotEmpty($hookSuffix);
    }

    /**
     * Test init method registers hooks.
     *
     * @covers ::init
     */
    public function testInit(): void
    {
        // Call init to register hooks.
        Page::init();

        // Verify hooks are registered.
        $this->assertNotFalse(has_action('admin_init', [Page::class, 'registerSettings']));
        $this->assertNotFalse(has_action('admin_post_add_category_override', [Page::class, 'handleAddCategoryOverride']));
        $this->assertNotFalse(has_action('admin_post_update_category_override', [Page::class, 'handleUpdateCategoryOverride']));
        $this->assertNotFalse(has_action('admin_post_delete_category_override', [Page::class, 'handleDeleteCategoryOverride']));
    }

    /**
     * Test registerSettings method.
     *
     * @covers ::registerSettings
     */
    public function testRegisterSettings(): void
    {
        global $wp_registered_settings;

        // Clear any existing registration.
        unset($wp_registered_settings[Settings::OPTION_KEY]);

        // Register settings.
        Page::registerSettings();

        // Verify setting is registered.
        $this->assertArrayHasKey(Settings::OPTION_KEY, $wp_registered_settings);
        $this->assertEquals('ai_summarize_settings_group', $wp_registered_settings[Settings::OPTION_KEY]['group']);
    }

    /**
     * Test handleAddCategoryOverride with invalid category.
     *
     * @covers ::handleAddCategoryOverride
     */
    public function testHandleAddCategoryOverrideInvalidCategory(): void
    {
        // Set up POST data with invalid category ID.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'add_category_override',
            'category_id'         => 99999, // Non-existent category.
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleAddCategoryOverride();

        // Verify error was added.
        $errors = get_settings_errors('ai_summarize_settings');
        $this->assertNotEmpty($errors);
        $this->assertEquals('invalid_category', $errors[0]['code']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test handleAddCategoryOverride with duplicate category.
     *
     * @covers ::handleAddCategoryOverride
     */
    public function testHandleAddCategoryOverrideDuplicate(): void
    {
        // Create a test category.
        $categoryId = $this->factory()->category->create(['name' => 'Test Category']);

        // Add existing override.
        $this->settings->updateCategoryOverrides([
            'category_' . $categoryId => [
                'prompt_template' => 'Existing prompt',
            ],
        ]);

        // Set up POST data to add duplicate.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'add_category_override',
            'category_id'         => $categoryId,
            'prompt_template'     => 'New prompt',
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleAddCategoryOverride();

        // Verify error was added.
        $errors = get_settings_errors('ai_summarize_settings');
        $this->assertNotEmpty($errors);
        $this->assertEquals('category_exists', $errors[0]['code']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test handleUpdateCategoryOverride with invalid category.
     *
     * @covers ::handleUpdateCategoryOverride
     */
    public function testHandleUpdateCategoryOverrideInvalidCategory(): void
    {
        // Set up POST data with invalid category ID.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'update_category_override',
            'category_id'         => 99999, // Non-existent category.
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleUpdateCategoryOverride();

        // Verify error was added.
        $errors = get_settings_errors('ai_summarize_settings');
        $this->assertNotEmpty($errors);
        $this->assertEquals('invalid_category', $errors[0]['code']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test handleDeleteCategoryOverride with invalid category.
     *
     * @covers ::handleDeleteCategoryOverride
     */
    public function testHandleDeleteCategoryOverrideInvalidCategory(): void
    {
        // Set up POST data with invalid category ID.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'delete_category_override',
            'category_id'         => 0, // Invalid category.
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleDeleteCategoryOverride();

        // Verify error was added.
        $errors = get_settings_errors('ai_summarize_settings');
        $this->assertNotEmpty($errors);
        $this->assertEquals('invalid_category', $errors[0]['code']);

        // Clean up.
        $_POST = [];
    }

    /**
     * Test handleDeleteCategoryOverride with non-existent override.
     *
     * @covers ::handleDeleteCategoryOverride
     */
    public function testHandleDeleteCategoryOverrideNonExistent(): void
    {
        // Create a test category without any override.
        $categoryId = $this->factory()->category->create(['name' => 'Test Category']);

        // Set up POST data.
        $_POST = [
            '_ai_summarize_nonce' => wp_create_nonce('ai_summarize_settings'),
            'action'              => 'delete_category_override',
            'category_id'         => $categoryId,
        ];

        // Mock current user capability.
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        // Handle the form submission.
        Page::handleDeleteCategoryOverride();

        // Should not add any error (silently succeeds).
        $errors = get_settings_errors('ai_summarize_settings');
        $this->assertEmpty($errors);

        // Clean up.
        $_POST = [];
    }
}
