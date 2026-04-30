<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php85;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.5 polyfill methods.
 *
 * @testCase
 */
class Php85Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testPhpBuildDateConstant(): void
    {
        Assert::same('Dec 28 2025 00:00:00', Php85::PHP_BUILD_DATE);
    }

    public function testPhpBuildProviderConstant(): void
    {
        Assert::same('phuture/continuum', Php85::PHP_BUILD_PROVIDER);
    }
}

(new Php85Test())->run();
