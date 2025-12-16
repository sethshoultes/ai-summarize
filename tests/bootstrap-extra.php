<?php

/**
 * PHPUnit bootstrap extra file.
 *
 * This file is included by bootstrap.php after after all default bootstrap steps
 * are run and before the WP test library's bootstrap file is run.
 *
 * @var string $testsDirectory     The full path to the WordPress testing library.
 * @var bool   $requireMemberPress Whether or not MemberPress is defined as a package dependency.
 */

 declare(strict_types=1);

 // phpcs:ignore PSR12.Files.FileHeader.IncorrectOrder -- not a file-level docblock.
 /**
  * Additional bootstrap steps to be run after the WP core "muplugins_loaded" hook.
  *
  * At priority 10, the default bootstrap will automatically include the tested plugin's
  * main PHP file.
  *
  * If MemberPress was defined as a dependency, it will be activated and it's install routine
  * will be run automatically as well.
  */

tests_add_filter(
    'muplugins_loaded',
    function (): void {
        // Add additional bootstrap code here.
    }
);
