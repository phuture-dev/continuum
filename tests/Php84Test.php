<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php84;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.4 polyfill methods.
 *
 * @testCase
 */
class Php84Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testPhpSbindirConstant(): void
    {
        Assert::same('/usr/local/sbin', Php84::PHP_SBINDIR);
    }
}

(new Php84Test())->run();
