<?php

/**
 * PHPUnit bootstrap file.
 *
 * This file is manages by the caseproof/ground-level-php-tests package. Do not directly modify
 * this file.
 *
 * To regenerate the file, execute `./vendor/bin/cspf-tests-php scaffold bootstrap`.
 *
 * If the tested package requires additional bootstrapping code, execute
 * `./vendor/bin/cspf-tests-php scaffold bootstrap-extra` to add the extra bootstrap
 * file into your project. You can safely add any extra bootstrapping code to this file.
 */

declare(strict_types=1);

/**
 * Activates an installed plugin.
 *
 * @param string $slug The plugin slug, eg "memberpress/memberpress.php".
 */
function _installPlugin(string $slug): void
{
    activate_plugin($slug);
}

$bootstrapFile = dirname(__FILE__);

$testsDirectory = getenv('WP_TESTS_DIR');
if (! $testsDirectory) {
    $testsDirBase = [
        $bootstrapFile,
        dirname($bootstrapFile),
    ];
    foreach ($testsDirBase as $dir) {
        if (is_dir("{$dir}/tmp/wordpress-tests-lib")) {
            $testsDirectory = "{$dir}/tmp/wordpress-tests-lib";
            break;
        }
    }
}

if (!$testsDirectory) {
    echo 'Error: tmp/wordpress-tests-lib is missing! Please run `composer run-script install:wp` ' .
         '(`./vendor/bin/cspf-tests-php install-wp`) to create it.' . PHP_EOL;
    exit(1);
}

// Used to skip dependencies.
define('TESTS_RUNNING', true);
define('GRDLVL_TESTING', true);

$vendorDir     = [
    $bootstrapFile . '/vendor',
    dirname($bootstrapFile) . '/vendor',
];
$autoloadFound = false;
foreach ($vendorDir as $dir) {
    $autoloadFound = file_exists($dir . '/autoload.php');
    if ($autoloadFound) {
        // Autoload.
        require_once $dir . '/autoload.php';
        break;
    }
}
if (!$autoloadFound) {
    echo 'Error: vendor/autoload.php is missing! Please run `composer update` to create it.' . PHP_EOL;
    exit(1);
}

// Give access to tests_add_filter() function.
require_once $testsDirectory . '/includes/functions.php';

/*
 * Manually load the plugin being tested and if MemberPress is a dependency
 * activate the plugin and run its install routine.
 */
tests_add_filter(
    'muplugins_loaded',
    function () use ($bootstrapFile): void {
        require dirname($bootstrapFile) . '/ai-summarize.php';
    }
);

$bootstrapExtraFile = $bootstrapFile . '/bootstrap-extra.php';
if (file_exists($bootstrapExtraFile)) {
    require_once $bootstrapExtraFile;
}

// Start up the WP testing environment.
require $testsDirectory . '/includes/bootstrap.php';
