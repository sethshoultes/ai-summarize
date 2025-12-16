<?php

/**
 * Plugin Name: AI Summarize
 * Plugin URI: https://github.com/caseproof/ai-fridays-session-1
 * Description: Add AI service buttons to blog posts for quick content summarization using popular AI services.
 * Author: Caseproof
 * Version: 0.1.0
 * Author URI: https://caseproof.com
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

/*
 * The vendor-prefixed autoloader is used to autoload classes from the Ground Level
 * framework.
 *
 * These classes are repnamespaced during a build step in order to prevent
 * conflicts with other plugins which may also utilize the Ground Level framework.
 */
require_once 'vendor-prefixed/autoload.php';

require_once 'src/functions.php';
use function Caseproof\AiSummarize\aiSummarizeApp;

return aiSummarizeApp( __FILE__ );
