<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use CurlHandle;
use mysqli_result;
use Phuture\Continuum\Php82;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for PHP 8.2 polyfill methods.
 *
 * @testCase
 */
class Php82Test extends TestCase
{
    // =========================================================================
    // curl_upkeep Tests
    // =========================================================================

    public function testCurlUpkeep(): void
    {
        if (!extension_loaded('curl')) {
            Assert::false(extension_loaded('curl'), 'curl extension is not loaded');

            return;
        }

        $handle = curl_init();
        Assert::type(CurlHandle::class, $handle);
        Assert::true(Php82::curl_upkeep($handle));
    }

    // =========================================================================
    // imap_is_open Tests
    // =========================================================================

    public function testImapIsOpen(): void
    {
        if (!extension_loaded('imap')) {
            Assert::false(extension_loaded('imap'), 'imap extension is not loaded');

            return;
        }

        $host = getenv('IMAP_HOST') ?: 'localhost';
        $port = getenv('IMAP_PORT') ?: '143';

        $mailbox = "{{$host}:{$port}/imap/novalidate-cert}";
        $imapStream = @imap_open($mailbox, '', '');

        if ($imapStream === false) {
            imap_errors(); // Clear errors to prevent PHP Request Shutdown notice

            Assert::false($imapStream, 'IMAP server is not available');

            return;
        }

        try {
            // Test that the polyfill correctly identifies an open IMAP connection
            Assert::true(Php82::imap_is_open($imapStream));
        } finally {
            imap_close($imapStream);
        }

        // Test with a non-IMAP resource (should return false)
        $fp = fopen('php://temp', 'r');
        try {
            Assert::false(Php82::imap_is_open($fp));
        } finally {
            fclose($fp);
        }
    }

    // =========================================================================
    // memory_reset_peak_usage Tests
    // =========================================================================

    public function testMemoryResetPeakUsage(): void
    {
        // This is a no-op, so it shouldn't throw anything
        Php82::memory_reset_peak_usage();
        Assert::true(true);
    }

    // =========================================================================
    // mysqli_execute_query Tests
    // =========================================================================

    public function testMysqliExecuteQuery(): void
    {
        if (!extension_loaded('mysqli')) {
            Assert::false(extension_loaded('mysqli'), 'mysqli extension is not loaded');

            return;
        }

        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $port = (int) (getenv('MYSQL_PORT') ?: '3306');
        $user = getenv('MYSQL_USER') ?: 'root';
        $pass = getenv('MYSQL_PASS') ?: 'root';

        $mysqli = @mysqli_connect($host, $user, $pass, '', $port);

        if ($mysqli === false) {
            Assert::false($mysqli, 'MySQL server is not available');

            return;
        }

        // Create a test database and table
        $result = mysqli_query($mysqli, 'CREATE DATABASE IF NOT EXISTS continuum_test');
        if ($result === false) {
            Assert::false($result, 'Failed to create test database');

            return;
        }

        mysqli_select_db($mysqli, 'continuum_test');
        mysqli_query($mysqli, 'DROP TABLE IF EXISTS test_table');
        mysqli_query($mysqli, 'CREATE TABLE test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), value INT)');

        try {
            // Test INSERT with parameters
            $result = Php82::mysqli_execute_query($mysqli, 'INSERT INTO test_table (name, value) VALUES (?, ?)', ['test', 42]);
            Assert::true($result);

            // Test SELECT with parameters
            $result = Php82::mysqli_execute_query($mysqli, 'SELECT * FROM test_table WHERE value = ?', [42]);
            Assert::type(mysqli_result::class, $result);

            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            Assert::count(1, $rows);
            Assert::same('test', $rows[0]['name']);
            Assert::same(42, $rows[0]['value']);

            // Test SELECT with multiple parameters
            $result = Php82::mysqli_execute_query($mysqli, 'INSERT INTO test_table (name, value) VALUES (?, ?)', ['foo', 100]);
            Assert::true($result);

            $result = Php82::mysqli_execute_query($mysqli, 'SELECT * FROM test_table WHERE value IN (?, ?)', [42, 100]);
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            Assert::count(2, $rows);

            // Test UPDATE with parameters
            $result = Php82::mysqli_execute_query($mysqli, 'UPDATE test_table SET value = ? WHERE name = ?', [99, 'test']);
            Assert::true($result);

            $result = Php82::mysqli_execute_query($mysqli, 'SELECT value FROM test_table WHERE name = ?', ['test']);
            $row = mysqli_fetch_assoc($result);
            Assert::same(99, $row['value']);

            // Test query without parameters
            $result = Php82::mysqli_execute_query($mysqli, 'SELECT COUNT(*) as count FROM test_table');
            $row = mysqli_fetch_assoc($result);
            Assert::same(2, $row['count']);

        } finally {
            // Clean up
            mysqli_query($mysqli, 'DROP TABLE IF EXISTS test_table');
            mysqli_query($mysqli, 'DROP DATABASE IF EXISTS continuum_test');
            mysqli_close($mysqli);
        }
    }

    // =========================================================================
    // openssl_cipher_key_length Tests
    // =========================================================================

    public function testOpensslCipherKeyLength(): void
    {
        if (!extension_loaded('openssl')) {
            Assert::false(extension_loaded('openssl'), 'openssl extension is not loaded');

            return;
        }

        // Test known ciphers
        Assert::same(16, Php82::openssl_cipher_key_length('AES-128-CBC'));
        Assert::same(32, Php82::openssl_cipher_key_length('AES-256-GCM'));
        Assert::same(8, Php82::openssl_cipher_key_length('DES-CBC'));

        // Case insensitivity
        Assert::same(16, Php82::openssl_cipher_key_length('aes-128-cbc'));

        // Test unknown
        Assert::false(Php82::openssl_cipher_key_length('UNKNOWN-CIPHER-XYZ'));
    }
}

(new Php82Test())->run();
