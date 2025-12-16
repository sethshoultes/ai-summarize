<?php

/**
 * AI Service URL Generator
 *
 * Generates properly formatted URLs for AI services with encoded prompts.
 *
 * @package Caseproof\AiSummarize
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Caseproof\AiSummarize\Services;

/**
 * URL Generator Service
 *
 * Handles URL generation for all supported AI services.
 */
class UrlGenerator {

	/**
	 * Generate URL for a specific AI service with encoded prompt
	 *
	 * Returns empty string for modal services (services with empty url_template).
	 *
	 * @param  string $service The AI service identifier.
	 * @param  string $prompt  The prompt to encode in the URL.
	 * @return string The generated URL, or empty string for modal services.
	 */
	public function generate( string $service, string $prompt ): string {
		if ( ! isset( Settings::AI_SERVICES[ $service ] ) ) {
			return '';
		}

		$serviceConfig = Settings::AI_SERVICES[ $service ];
		$urlTemplate   = $serviceConfig['url_template'];

		// Modal services have empty url_template.
		if ( empty( $urlTemplate ) ) {
			return '';
		}

		// URL encode the prompt.
		$encodedPrompt = rawurlencode( $prompt );

		// Generate the final URL.
		return sprintf( $urlTemplate, $encodedPrompt );
	}

	/**
	 * Get display name for a service
	 *
	 * @param  string $service The AI service identifier.
	 * @return string The service display name.
	 */
	public function getServiceName( string $service ): string {
		return Settings::AI_SERVICES[ $service ]['name'] ?? $service;
	}

	/**
	 * Get all supported service identifiers
	 *
	 * @return array<string> List of service identifiers.
	 */
	public function getSupportedServices(): array {
		return array_keys( Settings::AI_SERVICES );
	}

	/**
	 * Check if a service is supported
	 *
	 * @param  string $service The AI service identifier.
	 * @return boolean True if supported.
	 */
	public function isServiceSupported( string $service ): bool {
		return isset( Settings::AI_SERVICES[ $service ] );
	}
}
