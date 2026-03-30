<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php81;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.1 polyfill methods.
 *
 * @testCase
 */
class Php81Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testConstants(): void
    {
        Assert::same(19, Php81::IMAGETYPE_AVIF);
        Assert::same(256, Php81::IMG_AVIF);
        Assert::same(101, Php81::IMG_WEBP_LOSSLESS);
        Assert::same(64, Php81::MYSQLI_REFRESH_REPLICA);
        Assert::same(384, Php81::T_READONLY);
    }

    // =========================================================================
    // fsync Tests
    // =========================================================================

    public function testFsync(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'fsync_test');
        $fp = fopen($tempFile, 'w+');
        try {
            fwrite($fp, 'test data');
            Assert::true(Php81::fsync($fp));
        } finally {
            fclose($fp);
            @unlink($tempFile);
        }

        // Test with invalid resource
        Assert::false(Php81::fsync('not a resource'));
    }

    // =========================================================================
    // fdatasync Tests
    // =========================================================================

    public function testFdatasync(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'fdatasync_test');
        $fp = fopen($tempFile, 'w+');
        try {
            fwrite($fp, 'test data');
            Assert::true(Php81::fdatasync($fp));
        } finally {
            fclose($fp);
            @unlink($tempFile);
        }

        // Test with invalid resource
        Assert::false(Php81::fdatasync('not a resource'));
    }
}

(new Php81Test())->run();
