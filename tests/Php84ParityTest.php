<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php84;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php84 polyfill against native PHP 8.4+ functions.
 *
 * @testCase
 */
class Php84ParityTest extends TestCase
{
    private bool $hasNativeSbindir;

    protected function setUp(): void
    {
        $this->hasNativeSbindir = defined('PHP_SBINDIR');
    }

    // =========================================================================
    // Constants Parity Tests
    // =========================================================================

    public function testPhpSbindirConstantParity(): void
    {
        if (!$this->hasNativeSbindir) {
            Assert::false($this->hasNativeSbindir, 'Native PHP_SBINDIR not available');

            return;
        }

        Assert::same(defined('PHP_SBINDIR'), defined('Phuture\Continuum\Php84::PHP_SBINDIR'));
    }
}

(new Php84ParityTest())->run();
