<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize;

/**
 * The main application instance.
 *
 * This function returns a singleton instance of the Bootstrap class which
 * acts as the plugin's main entry point.
 *
 * @param  string $mainFile The main plugin file.
 * @return Bootstrap
 */
function aiSummarizeApp( string $mainFile ): Bootstrap {
	static $app = null;
	if ( is_null( $app ) ) {
		// Get plugin data.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( $mainFile );

		$app = new Bootstrap( $mainFile, $plugin_data );
	}
	return $app;
}
