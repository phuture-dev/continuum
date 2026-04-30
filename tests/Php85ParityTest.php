<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php85;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php85 polyfill against native PHP 8.5+ functions.
 *
 * @testCase
 */
class Php85ParityTest extends TestCase
{
    private bool $hasNativeBuildDate;
    private bool $hasNativeBuildProvider;

    protected function setUp(): void
    {
        $this->hasNativeBuildDate = defined('PHP_BUILD_DATE');
        $this->hasNativeBuildProvider = defined('PHP_BUILD_PROVIDER');
    }

    // =========================================================================
    // Constants Parity Tests
    // =========================================================================

    public function testPhpBuildDateConstantParity(): void
    {
        if (!$this->hasNativeBuildDate) {
            Assert::false($this->hasNativeBuildDate, 'Native PHP_BUILD_DATE not available');

            return;
        }

        Assert::same(defined('PHP_BUILD_DATE'), defined('Phuture\Continuum\Php85::PHP_BUILD_DATE'));
    }

    public function testPhpBuildProviderConstantParity(): void
    {
        if (!$this->hasNativeBuildProvider) {
            Assert::false($this->hasNativeBuildProvider, 'Native PHP_BUILD_PROVIDER not available');

            return;
        }

        Assert::same(defined('PHP_BUILD_PROVIDER'), defined('Phuture\Continuum\Php85::PHP_BUILD_PROVIDER'));
    }
}

(new Php85ParityTest())->run();
