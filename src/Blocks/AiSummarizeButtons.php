<?php

/**
 * AI Summarize Buttons Block
 *
 * WordPress block that renders AI service buttons for content summarization.
 *
 * @package Caseproof\AiSummarize
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Caseproof\AiSummarize\Blocks;

use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Services\UrlGenerator;

/**
 * AI Summarize Buttons Block Class
 *
 * Handles block registration and rendering for AI service buttons.
 */
class AiSummarizeButtons {

	/**
	 * Settings service instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * URL Generator service instance.
	 *
	 * @var UrlGenerator
	 */
	private UrlGenerator $urlGenerator;

	/**
	 * Constructor.
	 *
	 * @param Settings     $settings     Settings service.
	 * @param UrlGenerator $urlGenerator URL generator service.
	 */
	public function __construct( Settings $settings, UrlGenerator $urlGenerator ) {
		$this->settings     = $settings;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Register the block using block.json.
	 *
	 * @return void
	 */
	public function register(): void {
		$blockPath = dirname( plugin_dir_path( __FILE__ ), 2 ) . '/build/Blocks/ai-summarize-buttons';

		$blockType = register_block_type(
			$blockPath,
			[
				'render_callback' => [ $this, 'render' ],
			]
		);

		// Pass AI services data and global settings to JavaScript.
		if ( $blockType ) {
			$globalSettings = $this->settings->getSettings();

			wp_localize_script(
				'ai-summarize-buttons-editor-script',
				'aiSummarizeData',
				[
					'services' => Settings::AI_SERVICES,
				]
			);

			wp_localize_script(
				'ai-summarize-buttons-editor-script',
				'aiSummarizeGlobalSettings',
				[
					'enabledServices' => $globalSettings['enabled_services'] ?? [],
					'displayOptions'  => $globalSettings['display_options'] ?? [],
				]
			);
		}
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @param  array     $attributes Block attributes.
	 * @param  string    $content    Block content.
	 * @param  \WP_Block $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render( array $attributes, string $content, $block ): string {
		// Get current post context.
		$postId = get_the_ID();
		if ( ! $postId ) {
			return '';
		}

		// Determine settings source: block custom or global/category.
		$useGlobalSettings = $attributes['useGlobalSettings'] ?? true;

		// Get primary category.
		$categories      = get_the_category( $postId );
		$primaryCategory = ! empty( $categories ) ? $categories[0] : null;

		// Get prompt template (category override or global).
		$promptTemplate = $this->settings->getPromptForCategory( $primaryCategory );

		// Determine which services to use.
		if ( ! $useGlobalSettings && ! empty( $attributes['enabledServices'] ) ) {
			// Use block custom settings.
			$activeServices = $attributes['enabledServices'];
			$buttonSize     = $attributes['buttonSize'] ?? 'medium';
			$buttonLayout   = $attributes['buttonLayout'] ?? 'horizontal';
			$showLabels     = $attributes['showLabels'] ?? true;
		} else {
			// Use global/category settings.
			$settings        = $this->settings->getSettings();
			$enabledServices = $this->settings->getEnabledServicesForCategory( $primaryCategory );
			$activeServices  = array_keys( array_filter( $enabledServices ) );
			$buttonSize      = $settings['display_options']['button_size'] ?? 'medium';
			$buttonLayout    = $settings['display_options']['layout'] ?? 'horizontal';
			$showLabels      = $settings['display_options']['show_labels'] ?? true;
		}

		// If no services enabled, return empty.
		if ( empty( $activeServices ) ) {
			return '';
		}

		// Process placeholders in prompt.
		$processedPrompt = $this->settings->processPlaceholders( $promptTemplate );

		// Generate buttons HTML.
		$buttonsHtml = $this->renderButtons( $activeServices, $processedPrompt, $buttonSize, $buttonLayout, $showLabels );

		// Get alignment class.
		$alignClass = ! empty( $attributes['align'] ) ? ' align' . $attributes['align'] : '';

		// Return wrapped HTML.
		return sprintf(
			'<div class="wp-block-ai-summarize-buttons%s">%s</div>',
			esc_attr( $alignClass ),
			$buttonsHtml
		);
	}

	/**
	 * Render individual service buttons.
	 *
	 * @param  array   $services     List of enabled service identifiers.
	 * @param  string  $prompt       The processed prompt.
	 * @param  string  $buttonSize   Button size (small, medium, large).
	 * @param  string  $buttonLayout Layout type (horizontal, vertical, grid).
	 * @param  boolean $showLabels   Whether to show service labels.
	 * @return string Rendered buttons HTML.
	 */
	private function renderButtons(
		array $services,
		string $prompt,
		string $buttonSize = 'medium',
		string $buttonLayout = 'horizontal',
		bool $showLabels = true
	): string {
		$buttons = [];

		foreach ( $services as $service ) {
			if ( ! $this->urlGenerator->isServiceSupported( $service ) ) {
				continue;
			}

			$serviceConfig = Settings::AI_SERVICES[ $service ];
			$serviceName   = $serviceConfig['name'];
			// Translators: %s is the name of the AI service (e.g., ChatGPT, Claude).
			$ariaLabel = sprintf( __( 'Summarize with %s', 'ai-summarize' ), $serviceName );
			$icon      = $serviceConfig['icon'] ?? '';

			$labelHtml = $showLabels
				? sprintf( '<span class="ai-summarize-button__label">%s</span>', esc_html( $serviceName ) )
				: '';

			// Check if this is a modal service (empty url_template).
			$isModalService = empty( $serviceConfig['url_template'] );

			if ( $isModalService ) {
				// Modal service: render as link with data attributes (styled same as other buttons).
				$modalUrl  = $serviceConfig['modal_url'] ?? '';
				$buttons[] = sprintf(
					'<a href="#" class="ai-summarize-button ai-summarize-button--%s ai-summarize-button--modal" ' .
					'data-prompt="%s" data-service="%s" data-url="%s" aria-label="%s">
						%s
						%s
					</a>',
					esc_attr( $service ),
					esc_attr( $prompt ),
					esc_attr( $serviceName ),
					esc_url( $modalUrl ),
					esc_attr( $ariaLabel ),
					$icon,
					$labelHtml
				);
			} else {
				// Direct link service: render as anchor with URL.
				$url       = $this->urlGenerator->generate( $service, $prompt );
				$buttons[] = sprintf(
					'<a href="%s" class="ai-summarize-button ai-summarize-button--%s" ' .
					'target="_blank" rel="noopener noreferrer" aria-label="%s">
						%s
						%s
					</a>',
					esc_url( $url ),
					esc_attr( $service ),
					esc_attr( $ariaLabel ),
					$icon,
					$labelHtml
				);
			}
		}

		$containerClasses = sprintf(
			'ai-summarize-buttons ai-summarize-buttons--size-%s ai-summarize-buttons--layout-%s',
			esc_attr( $buttonSize ),
			esc_attr( $buttonLayout )
		);

		return sprintf( '<div class="%s">%s</div>', $containerClasses, implode( '', $buttons ) );
	}
}
