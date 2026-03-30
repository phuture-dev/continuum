<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Phuture\Continuum\Php83;
use RuntimeException;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for Php83 polyfill methods.
 *
 * @testCase
 */
class Php83Test extends TestCase
{
    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testPosixConstants(): void
    {
        Assert::same(8, Php83::POSIX_PC_ALLOC_SIZE_MIN);
        Assert::same(6, Php83::POSIX_PC_CHOWN_RESTRICTED);
        Assert::same(0, Php83::POSIX_PC_LINK_MAX);
        Assert::same(1, Php83::POSIX_PC_MAX_CANON);
        Assert::same(2, Php83::POSIX_PC_MAX_INPUT);
        Assert::same(3, Php83::POSIX_PC_NAME_MAX);
        Assert::same(7, Php83::POSIX_PC_NO_TRUNC);
        Assert::same(4, Php83::POSIX_PC_PATH_MAX);
        Assert::same(5, Php83::POSIX_PC_PIPE_BUF);
        Assert::same(9, Php83::POSIX_PC_SYMLINK_MAX);
        Assert::same(0, Php83::POSIX_F_OK);
        Assert::same(4, Php83::POSIX_R_OK);
        Assert::same(2, Php83::POSIX_W_OK);
        Assert::same(1, Php83::POSIX_X_OK);
    }

    // =========================================================================
    // posix_eaccess Tests
    // =========================================================================

    public function testPosixEaccess(): void
    {
        if (!extension_loaded('posix')) {
            Assert::false(extension_loaded('posix'), 'posix extension is not loaded');
            return;
        }

        // Test file that exists
        Assert::true(Php83::posix_eaccess(__FILE__, Php83::POSIX_F_OK));
        Assert::true(Php83::posix_eaccess(__FILE__, Php83::POSIX_R_OK));

        // Test non-existent file
        Assert::false(Php83::posix_eaccess(__DIR__ . '/non_existent_file.txt', Php83::POSIX_F_OK));

        // Let's create a temporary file to test permissions
        $tempFile = tempnam(sys_get_temp_dir(), 'php83_test');
        if ($tempFile !== false) {
            chmod($tempFile, 0400); // Read only
            Assert::true(Php83::posix_eaccess($tempFile, Php83::POSIX_R_OK));
            Assert::false(Php83::posix_eaccess($tempFile, Php83::POSIX_W_OK));

            chmod($tempFile, 0600); // Read/Write
            Assert::true(Php83::posix_eaccess($tempFile, Php83::POSIX_R_OK | Php83::POSIX_W_OK));

            unlink($tempFile);
        }
    }
}

(new Php83Test())->run();
