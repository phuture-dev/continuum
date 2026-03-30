<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php81;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php81 polyfill against native PHP 8.1+ functions.
 *
 * @testCase
 */
class Php81ParityTest extends TestCase
{
    private bool $hasFsync;
    private bool $hasFdatasync;

    protected function setUp(): void
    {
        $this->hasFsync = function_exists('fsync');
        $this->hasFdatasync = function_exists('fdatasync');
    }

    // =========================================================================
    // Constants Parity Tests
    // =========================================================================

    public function testConstantsParity(): void
    {
        if (defined('IMAGETYPE_AVIF')) {
            Assert::same(constant('IMAGETYPE_AVIF'), Php81::IMAGETYPE_AVIF);
        }

        if (defined('IMG_AVIF')) {
            Assert::same(constant('IMG_AVIF'), Php81::IMG_AVIF);
        }

        if (defined('IMG_WEBP_LOSSLESS')) {
            Assert::same(constant('IMG_WEBP_LOSSLESS'), Php81::IMG_WEBP_LOSSLESS);
        }

        if (defined('MYSQLI_REFRESH_REPLICA')) {
            Assert::same(@constant('MYSQLI_REFRESH_REPLICA'), Php81::MYSQLI_REFRESH_REPLICA);
        }

        if (defined('T_READONLY')) {
            Assert::same(constant('T_READONLY'), Php81::T_READONLY);
        }
    }

    // =========================================================================
    // fsync Parity Tests
    // =========================================================================

    public function testFsyncParity(): void
    {
        if (!$this->hasFsync) {
            Assert::false($this->hasFsync, 'Native fsync() not available');

            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'fsync_parity');
        $fp = fopen($tempFile, 'w+');
        try {
            fwrite($fp, 'test data');
            $native = fsync($fp); // @phpstan-ignore function.notFound
            $polyfill = Php81::fsync($fp);

            Assert::same($native, $polyfill);
        } finally {
            fclose($fp);
            @unlink($tempFile);
        }
    }

    // =========================================================================
    // fdatasync Parity Tests
    // =========================================================================

    public function testFdatasyncParity(): void
    {
        if (!$this->hasFdatasync) {
            Assert::false($this->hasFdatasync, 'Native fdatasync() not available');

            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'fdatasync_parity');
        $fp = fopen($tempFile, 'w+');
        try {
            fwrite($fp, 'test data');
            $native = fdatasync($fp); // @phpstan-ignore function.notFound
            $polyfill = Php81::fdatasync($fp);

            Assert::same($native, $polyfill);
        } finally {
            fclose($fp);
            @unlink($tempFile);
        }
    }
}

(new Php81ParityTest())->run();
