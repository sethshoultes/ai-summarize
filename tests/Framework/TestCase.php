<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Tests\Framework;

use GroundLevel\Testing\WPTestCase as CaseproofWPTestCase;

/**
 * Base unit test case class which all test cases extends.
 *
 * This file extends the base Caseproof library test case which extends the
 * WP_TestCase class which extends the base PHPUnit TestCase class.
 */
abstract class TestCase extends CaseproofWPTestCase
{
}
