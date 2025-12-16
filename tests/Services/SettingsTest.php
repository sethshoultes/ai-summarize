<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests\Services;

use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Tests\Framework\TestCase;
use WP_Term;

/**
 * Unit tests for the Settings service.
 *
 * @coversDefaultClass \Caseproof\AiSummarize\Services\Settings
 */
class SettingsTest extends TestCase
{
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
        $this->settings = new Settings();

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

        parent::tearDown();
    }

    /**
     * Test getting default settings when no options exist.
     *
     * @covers ::getSettings
     * @covers ::getDefaults
     */
    public function testGetSettingsReturnsDefaults(): void
    {
        $settings = $this->settings->getSettings();
        $defaults = Settings::getDefaults();

        $this->assertEquals($defaults, $settings);
        $this->assertArrayHasKey('version', $settings);
        $this->assertArrayHasKey('global_prompt', $settings);
        $this->assertArrayHasKey('enabled_services', $settings);
        $this->assertArrayHasKey('display_options', $settings);
    }

    /**
     * Test updating and retrieving settings.
     *
     * @covers ::updateSettings
     * @covers ::getSettings
     */
    public function testUpdateAndGetSettings(): void
    {
        $newSettings = [
            'version'          => '2.0.0',
            'global_prompt'    => 'Custom prompt template with [[URL]]',
            'enabled_services' => [
                'chatgpt'    => true,
                'perplexity' => false,
                'claude'     => true,
                'copilot'    => false,
                'you'        => false,
            ],
            'display_options'  => [
                'button_size' => 'large',
                'show_labels' => false,
                'layout'      => 'vertical',
            ],
        ];

        $result = $this->settings->updateSettings($newSettings);
        $this->assertTrue($result);

        $retrieved = $this->settings->getSettings();
        $this->assertEquals($newSettings, $retrieved);
    }

    /**
     * Test getting a specific setting.
     *
     * @covers ::getSetting
     */
    public function testGetSpecificSetting(): void
    {
        $testSettings = [
            'global_prompt' => 'Test prompt',
            'version'       => '1.5.0',
        ];

        $this->settings->updateSettings($testSettings);

        $this->assertEquals('Test prompt', $this->settings->getSetting('global_prompt'));
        $this->assertEquals('1.5.0', $this->settings->getSetting('version'));
        $this->assertNull($this->settings->getSetting('nonexistent'));
        $this->assertEquals('default', $this->settings->getSetting('nonexistent', 'default'));
    }

    /**
     * Test updating a specific setting.
     *
     * @covers ::updateSetting
     */
    public function testUpdateSpecificSetting(): void
    {
        // Set initial settings.
        $initial = Settings::getDefaults();
        $this->settings->updateSettings($initial);

        // Update specific setting.
        $result = $this->settings->updateSetting('global_prompt', 'New prompt template');
        $this->assertTrue($result);

        // Verify only the specific setting changed.
        $updated = $this->settings->getSettings();
        $this->assertEquals('New prompt template', $updated['global_prompt']);
        $this->assertEquals($initial['version'], $updated['version']);
        $this->assertEquals($initial['enabled_services'], $updated['enabled_services']);
    }

    /**
     * Test category overrides functionality.
     *
     * @covers ::getCategoryOverrides
     * @covers ::updateCategoryOverrides
     */
    public function testCategoryOverrides(): void
    {
        // Initially empty.
        $overrides = $this->settings->getCategoryOverrides();
        $this->assertEmpty($overrides);

        // Add some overrides.
        $testOverrides = [
            'category_1' => [
                'prompt_template'  => 'Custom prompt for category 1',
                'enabled_services' => ['chatgpt', 'claude'],
            ],
            'category_2' => [
                'prompt_template'  => 'Different prompt for category 2',
                'enabled_services' => ['perplexity'],
            ],
        ];

        $result = $this->settings->updateCategoryOverrides($testOverrides);
        $this->assertTrue($result);

        $retrieved = $this->settings->getCategoryOverrides();

        // Check the structure is correct after sanitization.
        $this->assertArrayHasKey('category_1', $retrieved);
        $this->assertArrayHasKey('category_2', $retrieved);
        $this->assertEquals('Custom prompt for category 1', $retrieved['category_1']['prompt_template']);
        $this->assertEquals('Different prompt for category 2', $retrieved['category_2']['prompt_template']);

        // Check services are converted to boolean array format.
        $this->assertArrayHasKey('enabled_services', $retrieved['category_1']);
        $this->assertArrayHasKey('chatgpt', $retrieved['category_1']['enabled_services']);
        $this->assertArrayHasKey('claude', $retrieved['category_1']['enabled_services']);
        $this->assertTrue($retrieved['category_1']['enabled_services']['chatgpt']);
        $this->assertTrue($retrieved['category_1']['enabled_services']['claude']);
    }

    /**
     * Test getting prompt for category with override.
     *
     * @covers ::getPromptForCategory
     */
    public function testGetPromptForCategoryWithOverride(): void
    {
        // Set up global and category-specific prompts.
        $this->settings->updateSettings(['global_prompt' => 'Global prompt template']);

        $overrides = [
            'category_123' => [
                'prompt_template' => 'Category-specific prompt template',
            ],
        ];
        $this->settings->updateCategoryOverrides($overrides);

        // Create a mock category term.
        $category = new WP_Term((object) [
            'term_id' => 123,
            'name'    => 'Test Category',
        ]);

        // Test category-specific prompt.
        $categoryPrompt = $this->settings->getPromptForCategory($category);
        $this->assertEquals('Category-specific prompt template', $categoryPrompt);

        // Test fallback to global prompt.
        $globalPrompt = $this->settings->getPromptForCategory(null);
        $this->assertEquals('Global prompt template', $globalPrompt);

        // Test category without override falls back to global.
        $otherCategory  = new WP_Term((object) [
            'term_id' => 456,
            'name'    => 'Other Category',
        ]);
        $fallbackPrompt = $this->settings->getPromptForCategory($otherCategory);
        $this->assertEquals('Global prompt template', $fallbackPrompt);
    }

    /**
     * Test getting enabled services for category.
     *
     * @covers ::getEnabledServicesForCategory
     */
    public function testGetEnabledServicesForCategory(): void
    {
        $globalServices = [
            'chatgpt'    => true,
            'perplexity' => true,
            'claude'     => false,
            'copilot'    => false,
            'you'        => true,
        ];

        $this->settings->updateSettings(['enabled_services' => $globalServices]);

        $overrides = [
            'category_123' => [
                'enabled_services' => ['chatgpt', 'claude'],
            ],
        ];
        $this->settings->updateCategoryOverrides($overrides);

        $category = new WP_Term((object) [
            'term_id' => 123,
            'name'    => 'Test Category',
        ]);

        // Test category-specific services.
        $categoryServices = $this->settings->getEnabledServicesForCategory($category);
        $this->assertArrayHasKey('chatgpt', $categoryServices);
        $this->assertArrayHasKey('claude', $categoryServices);
        $this->assertTrue($categoryServices['chatgpt']);
        $this->assertTrue($categoryServices['claude']);

        // Test fallback to global services.
        $globalServicesRetrieved = $this->settings->getEnabledServicesForCategory(null);
        $this->assertEquals($globalServices, $globalServicesRetrieved);
    }

    /**
     * Test placeholder processing.
     *
     * @covers ::processPlaceholders
     */
    public function testPlaceholderProcessing(): void
    {
        $template = 'Please summarize [[TITLE]] from [[WEBSITE]] at [[URL]]. Category: [[CATEGORY]]';

        $context = [
            'title'    => 'Sample Post Title',
            'website'  => 'Example Site',
            'url'      => 'https://example.com/post',
            'category' => 'Technology',
        ];

        $processed = $this->settings->processPlaceholders($template, $context);

        $expected = 'Please summarize Sample Post Title from Example Site at https://example.com/post. Category: Technology';
        $this->assertEquals($expected, $processed);
    }

    /**
     * Test settings sanitization.
     *
     * @covers ::sanitizeSettings
     */
    public function testSettingsSanitization(): void
    {
        $rawInput = [
            'version'          => '<script>alert("xss")</script>1.0.0',
            'global_prompt'    => '<script>evil</script>Safe prompt with <strong>formatting</strong>',
            'enabled_services' => [
                'chatgpt'     => '1',
                'perplexity'  => '',
                'claude'      => 'yes',
                'invalid_key' => true,
            ],
            'display_options'  => [
                'button_size'  => 'invalid_size',
                'show_labels'  => 'yes',
                'layout'       => 'invalid_layout',
                'extra_option' => 'should_be_ignored',
            ],
        ];

        $sanitized = $this->settings->sanitizeSettings($rawInput);

        // Check version is sanitized.
        $this->assertEquals('1.0.0', $sanitized['version']);

        // Check HTML is properly handled.
        $this->assertStringContainsString('Safe prompt with <strong>formatting</strong>', $sanitized['global_prompt']);
        $this->assertStringNotContainsString('<script>', $sanitized['global_prompt']);

        // Check services are properly boolean-ized.
        $this->assertTrue($sanitized['enabled_services']['chatgpt']);
        $this->assertFalse($sanitized['enabled_services']['perplexity']);
        $this->assertTrue($sanitized['enabled_services']['claude']);
        $this->assertArrayNotHasKey('invalid_key', $sanitized['enabled_services']);

        // Check display options are validated.
        $this->assertEquals('medium', $sanitized['display_options']['button_size']); // Default fallback.
        $this->assertTrue($sanitized['display_options']['show_labels']);
        $this->assertEquals('horizontal', $sanitized['display_options']['layout']); // Default fallback.
        $this->assertArrayNotHasKey('extra_option', $sanitized['display_options']);
    }

    /**
     * Test category overrides sanitization.
     *
     * @covers ::sanitizeCategoryOverrides
     */
    public function testCategoryOverridesSanitization(): void
    {
        $rawOverrides = [
            'category_123' => [
                'prompt_template'  => '<script>bad</script>Good prompt with <em>emphasis</em>',
                'enabled_services' => ['chatgpt', 'invalid_service', 'claude'],
            ],
            'invalid_key'  => [
                'prompt_template' => 'Should be ignored',
            ],
            'category_456' => [
                'enabled_services' => ['perplexity'],
            ],
        ];

        // Use reflection to access private method.
        $reflection = new \ReflectionClass($this->settings);
        $method     = $reflection->getMethod('sanitizeCategoryOverrides');
        $method->setAccessible(true);

        $sanitized = $method->invoke($this->settings, $rawOverrides);

        // Check valid category is processed.
        $this->assertArrayHasKey('category_123', $sanitized);
        $this->assertStringContainsString('Good prompt with <em>emphasis</em>', $sanitized['category_123']['prompt_template']);
        $this->assertStringNotContainsString('<script>', $sanitized['category_123']['prompt_template']);

        // Check services are properly filtered.
        $this->assertArrayHasKey('chatgpt', $sanitized['category_123']['enabled_services']);
        $this->assertArrayHasKey('claude', $sanitized['category_123']['enabled_services']);
        $this->assertArrayNotHasKey('invalid_service', $sanitized['category_123']['enabled_services']);

        // Check invalid key is ignored.
        $this->assertArrayNotHasKey('invalid_key', $sanitized);

        // Check category with only services.
        $this->assertArrayHasKey('category_456', $sanitized);
        $this->assertArrayHasKey('perplexity', $sanitized['category_456']['enabled_services']);
    }

    /**
     * Test reset to defaults functionality.
     *
     * @covers ::resetToDefaults
     */
    public function testResetToDefaults(): void
    {
        // Set custom settings.
        $customSettings = [
            'global_prompt'    => 'Custom prompt',
            'enabled_services' => [
                'chatgpt' => false,
                'claude'  => true,
            ],
        ];
        $this->settings->updateSettings($customSettings);

        // Verify custom settings are set.
        $current = $this->settings->getSettings();
        $this->assertEquals('Custom prompt', $current['global_prompt']);

        // Reset to defaults.
        $result = $this->settings->resetToDefaults();
        $this->assertTrue($result);

        // Verify settings are back to defaults.
        $reset    = $this->settings->getSettings();
        $defaults = Settings::getDefaults();
        $this->assertEquals($defaults, $reset);
    }

    /**
     * Test delete all settings functionality.
     *
     * @covers ::deleteAllSettings
     */
    public function testDeleteAllSettings(): void
    {
        // Set up some data.
        $this->settings->updateSettings(['global_prompt' => 'Test']);
        $this->settings->updateCategoryOverrides(['category_1' => ['prompt_template' => 'Test']]);
        update_option(Settings::VERSION_KEY, '1.0.0');

        // Verify data exists.
        $this->assertNotFalse(get_option(Settings::OPTION_KEY));
        $this->assertNotFalse(get_option(Settings::CATEGORY_OVERRIDES_KEY));
        $this->assertNotFalse(get_option(Settings::VERSION_KEY));

        // Delete all settings.
        $result = $this->settings->deleteAllSettings();
        $this->assertTrue($result);

        // Verify all data is removed.
        $this->assertFalse(get_option(Settings::OPTION_KEY));
        $this->assertFalse(get_option(Settings::CATEGORY_OVERRIDES_KEY));
        $this->assertFalse(get_option(Settings::VERSION_KEY));
    }

    /**
     * Test constants are properly defined.
     *
     * @covers ::AI_SERVICES
     * @covers ::PLACEHOLDERS
     */
    public function testConstants(): void
    {
        // Test AI services constant.
        $this->assertIsArray(Settings::AI_SERVICES);
        $this->assertArrayHasKey('chatgpt', Settings::AI_SERVICES);
        $this->assertArrayHasKey('perplexity', Settings::AI_SERVICES);
        $this->assertArrayHasKey('claude', Settings::AI_SERVICES);
        $this->assertArrayHasKey('copilot', Settings::AI_SERVICES);
        $this->assertArrayHasKey('you', Settings::AI_SERVICES);

        // Test placeholders constant.
        $this->assertIsArray(Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[URL]]', Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[WEBSITE]]', Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[TAGLINE]]', Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[TITLE]]', Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[EXCERPT]]', Settings::PLACEHOLDERS);
        $this->assertArrayHasKey('[[CATEGORY]]', Settings::PLACEHOLDERS);

        // Test option keys.
        $this->assertEquals('ai_summarize_settings', Settings::OPTION_KEY);
        $this->assertEquals('ai_summarize_category_overrides', Settings::CATEGORY_OVERRIDES_KEY);
        $this->assertEquals('ai_summarize_version', Settings::VERSION_KEY);
    }
}
