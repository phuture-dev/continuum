<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php83;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php83 polyfill against native PHP 8.3+ functions.
 *
 * @testCase
 */
class Php83ParityTest extends TestCase
{
    private bool $hasNativePosix;

    protected function setUp(): void
    {
        $this->hasNativePosix = function_exists('posix_eaccess');
    }

    // =========================================================================
    // posix_eaccess Parity Tests
    // =========================================================================

    public function testPosixEaccessParity(): void
    {
        if (!$this->hasNativePosix) {
            Assert::false($this->hasNativePosix, 'Native posix_eaccess() not available');
            return;
        }

        $testCases = [
            [__FILE__, Php83::POSIX_F_OK],
            [__FILE__, Php83::POSIX_R_OK],
            [__FILE__, Php83::POSIX_W_OK],
            [__FILE__, Php83::POSIX_X_OK],
            [__DIR__ . '/non_existent_file.txt', Php83::POSIX_F_OK],
            [__DIR__ . '/non_existent_file.txt', Php83::POSIX_R_OK],
        ];

        foreach ($testCases as $args) {
            $native = posix_eaccess($args[0], $args[1]);
            $polyfill = Php83::posix_eaccess($args[0], $args[1]);
            Assert::same($native, $polyfill, "Parity failed for posix_eaccess('{$args[0]}', {$args[1]})");
        }

        // Test temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'php83_parity_test');
        if ($tempFile !== false) {
            chmod($tempFile, 0400); // Read only
            Assert::same(
                posix_eaccess($tempFile, Php83::POSIX_R_OK),
                Php83::posix_eaccess($tempFile, Php83::POSIX_R_OK)
            );
            Assert::same(
                posix_eaccess($tempFile, Php83::POSIX_W_OK),
                Php83::posix_eaccess($tempFile, Php83::POSIX_W_OK)
            );

            chmod($tempFile, 0600); // Read/Write
            Assert::same(
                posix_eaccess($tempFile, Php83::POSIX_R_OK | Php83::POSIX_W_OK),
                Php83::posix_eaccess($tempFile, Php83::POSIX_R_OK | Php83::POSIX_W_OK)
            );

            unlink($tempFile);
        }
    }
}

(new Php83ParityTest())->run();
