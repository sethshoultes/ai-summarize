<?php

/**
 * Plugin Name: AI Summarize
 * Plugin URI: https://github.com/sethshoultes/ai-summarize
 * Description: Add AI service buttons to blog posts for quick content summarization using popular AI services.
 * Author: Caseproof
 * Version: 0.1.0
 * Author URI: https://github.com/sethshoultes
 * License: GPLv3
 * Text Domain: ai-summarize
 * Requires at least: 6.6
 * Tested up to: 6.8
 * Requires PHP: 8.2
 */

declare(strict_types=1);

/*
 * The default composer autoloader is used to autoload the plugin's classes.
 */
require_once 'vendor/autoload.php';

require_once 'src/functions.php';
use function Caseproof\AiSummarize\aiSummarizeApp;

/*
 * Initialize the plugin on the 'init' hook to ensure translations
 * are loaded at the correct time (WordPress 6.7+ requirement).
 */
add_action(
	'init',
	function () {
		aiSummarizeApp( __FILE__ );
	}
);
