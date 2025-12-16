<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests\Blocks;

use Caseproof\AiSummarize\Blocks\AiSummarizeButtons;
use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Services\UrlGenerator;
use GroundLevel\Testing\WPTestCase;

class AiSummarizeButtonsTest extends WPTestCase
{
    private AiSummarizeButtons $block;
    private Settings $settings;
    private UrlGenerator $urlGenerator;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings     = new Settings();
        $this->urlGenerator = new UrlGenerator();
        $this->block        = new AiSummarizeButtons($this->settings, $this->urlGenerator);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Clean up settings.
        $this->settings->deleteAllSettings();
    }

    public function testBlockRegistration(): void
    {
        $registry = \WP_Block_Type_Registry::get_instance();

        // Unregister first if already registered.
        if ($registry->is_registered('ai-summarize/buttons')) {
            $registry->unregister('ai-summarize/buttons');
        }

        $this->block->register();

        $this->assertTrue($registry->is_registered('ai-summarize/buttons'));
    }

    public function testRenderWithNoPost(): void
    {
        // Outside the loop, no post context.
        $output = $this->block->render([], '', null);

        $this->assertSame('', $output);
    }

    public function testRenderWithEnabledServices(): void
    {
        // Create a test post.
        $postId = $this->factory()->post->create([
            'post_title'   => 'Test Post',
            'post_content' => 'Test content',
        ]);

        // Set global post context.
        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        // Enable ChatGPT and Claude.
        $this->settings->updateSettings([
            'enabled_services' => [
                'chatgpt'    => true,
                'perplexity' => false,
                'claude'     => true,
                'copilot'    => false,
                'you'        => false,
            ],
            'global_prompt'    => 'Summarize [[TITLE]]',
        ]);

        $output = $this->block->render([], '', null);

        // Should contain buttons.
        $this->assertStringContainsString('ai-summarize-buttons', $output);
        $this->assertStringContainsString('ai-summarize-button--chatgpt', $output);
        $this->assertStringContainsString('ai-summarize-button--claude', $output);
        $this->assertStringNotContainsString('ai-summarize-button--perplexity', $output);

        // Should contain service names.
        $this->assertStringContainsString('ChatGPT', $output);
        $this->assertStringContainsString('Claude', $output);

        wp_reset_postdata();
    }

    public function testRenderWithNoEnabledServices(): void
    {
        // Create a test post.
        $postId = $this->factory()->post->create([
            'post_title' => 'Test Post',
        ]);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        // Disable all services.
        $this->settings->updateSettings([
            'enabled_services' => [
                'chatgpt'    => false,
                'perplexity' => false,
                'claude'     => false,
                'copilot'    => false,
                'you'        => false,
            ],
        ]);

        $output = $this->block->render([], '', null);

        // Should return empty.
        $this->assertSame('', $output);

        wp_reset_postdata();
    }

    public function testRenderWithCategoryOverride(): void
    {
        // Create a category.
        $categoryId = $this->factory()->category->create(['name' => 'Test Category']);

        // Create a post in that category.
        $postId = $this->factory()->post->create([
            'post_title'    => 'Test Post',
            'post_category' => [$categoryId],
        ]);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        // Set global settings.
        $this->settings->updateSettings([
            'enabled_services' => [
                'chatgpt'    => true,
                'perplexity' => true,
                'claude'     => false,
                'copilot'    => false,
                'you'        => false,
            ],
            'global_prompt'    => 'Global prompt',
        ]);

        // Set category override.
        $this->settings->updateCategoryOverrides([
            'category_' . $categoryId => [
                'prompt_template'  => 'Category-specific prompt',
                'enabled_services' => [
                    'claude' => true,
                    'you'    => true,
                ],
            ],
        ]);

        $output = $this->block->render([], '', null);

        // Should use category override settings.
        $this->assertStringContainsString('ai-summarize-button--claude', $output);
        $this->assertStringContainsString('ai-summarize-button--you', $output);
        $this->assertStringNotContainsString('ai-summarize-button--chatgpt', $output);

        wp_reset_postdata();
    }

    public function testRenderWithAlignment(): void
    {
        $postId = $this->factory()->post->create(['post_title' => 'Test']);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        $this->settings->updateSettings([
            'enabled_services' => ['chatgpt' => true],
        ]);

        $output = $this->block->render(['align' => 'left'], '', null);

        $this->assertStringContainsString('alignleft', $output);

        wp_reset_postdata();
    }

    public function testRenderContainsProperLinks(): void
    {
        $postId = $this->factory()->post->create(['post_title' => 'Test Post']);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        $this->settings->updateSettings([
            'enabled_services' => ['chatgpt' => true],
            'global_prompt'    => 'Test prompt',
        ]);

        $output = $this->block->render([], '', null);

        // Should contain proper link attributes.
        $this->assertStringContainsString('target="_blank"', $output);
        $this->assertStringContainsString('rel="noopener noreferrer"', $output);
        $this->assertStringContainsString('aria-label=', $output);

        wp_reset_postdata();
    }

    public function testRenderWithBlockCustomSettings(): void
    {
        $postId = $this->factory()->post->create(['post_title' => 'Test Post']);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        // Set global settings (should be ignored).
        $this->settings->updateSettings([
            'enabled_services' => ['chatgpt' => true],
            'global_prompt'    => 'Global prompt',
            'display_options'  => [
                'button_size' => 'small',
                'layout'      => 'vertical',
                'show_labels' => false,
            ],
        ]);

        // Use block custom settings.
        $attributes = [
            'useGlobalSettings' => false,
            'enabledServices'   => ['claude', 'perplexity'],
            'buttonSize'        => 'large',
            'buttonLayout'      => 'grid',
            'showLabels'        => true,
        ];

        $output = $this->block->render($attributes, '', null);

        // Should use block custom settings, not global.
        $this->assertStringContainsString('ai-summarize-button--claude', $output);
        $this->assertStringContainsString('ai-summarize-button--perplexity', $output);
        $this->assertStringNotContainsString('ai-summarize-button--chatgpt', $output);

        // Should use custom size and layout.
        $this->assertStringContainsString('ai-summarize-buttons--size-large', $output);
        $this->assertStringContainsString('ai-summarize-buttons--layout-grid', $output);

        // Should show labels.
        $this->assertStringContainsString('Claude', $output);
        $this->assertStringContainsString('Perplexity', $output);

        wp_reset_postdata();
    }

    public function testRenderWithBlockCustomSettingsDefaults(): void
    {
        $postId = $this->factory()->post->create(['post_title' => 'Test Post']);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        $this->settings->updateSettings([
            'global_prompt' => 'Test prompt',
        ]);

        // Use block custom settings with minimal attributes (test defaults).
        $attributes = [
            'useGlobalSettings' => false,
            'enabledServices'   => ['chatgpt'],
            // buttonSize, buttonLayout, showLabels should use defaults.
        ];

        $output = $this->block->render($attributes, '', null);

        // Should use default values.
        $this->assertStringContainsString('ai-summarize-buttons--size-medium', $output);
        $this->assertStringContainsString('ai-summarize-buttons--layout-horizontal', $output);
        $this->assertStringContainsString('ChatGPT', $output); // showLabels defaults to true.

        wp_reset_postdata();
    }

    public function testRenderWithBlockCustomSettingsEmpty(): void
    {
        $postId = $this->factory()->post->create(['post_title' => 'Test Post']);

        global $post;
        $post = get_post($postId);
        setup_postdata($post);

        // Disable all global services.
        $this->settings->updateSettings([
            'global_prompt'    => 'Test prompt',
            'enabled_services' => [
                'chatgpt'    => false,
                'perplexity' => false,
                'claude'     => false,
                'copilot'    => false,
                'you'        => false,
            ],
        ]);

        // Use block custom settings with empty services array.
        // Should fall through to global settings (which have no services enabled).
        $attributes = [
            'useGlobalSettings' => false,
            'enabledServices'   => [],
        ];

        $output = $this->block->render($attributes, '', null);

        // Should return empty when no services enabled (falls back to global).
        $this->assertSame('', $output);

        wp_reset_postdata();
    }
}
