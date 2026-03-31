<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use mysqli_result;
use Phuture\Continuum\Php82;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for Php82 polyfill against native PHP 8.2+ functions.
 *
 * @testCase
 */
class Php82ParityTest extends TestCase
{
    private bool $hasCurlUpkeep;
    private bool $hasImapIsOpen;
    private bool $hasMemoryResetPeakUsage;
    private bool $hasMysqliExecuteQuery;
    private bool $hasOpensslCipherKeyLength;

    protected function setUp(): void
    {
        $this->hasCurlUpkeep = function_exists('curl_upkeep');
        $this->hasImapIsOpen = function_exists('imap_is_open');
        $this->hasMemoryResetPeakUsage = function_exists('memory_reset_peak_usage');
        $this->hasMysqliExecuteQuery = function_exists('mysqli_execute_query');
        $this->hasOpensslCipherKeyLength = function_exists('openssl_cipher_key_length');
    }

    // =========================================================================
    // curl_upkeep Parity Tests
    // =========================================================================

    public function testCurlUpkeepParity(): void
    {
        if (!$this->hasCurlUpkeep) {
            Assert::false($this->hasCurlUpkeep, 'Native curl_upkeep() not available');

            return;
        }

        $handle = curl_init();
        $native = curl_upkeep($handle); // @phpstan-ignore function.notFound
        $polyfill = Php82::curl_upkeep($handle);

        Assert::same($native, $polyfill);
    }

    // =========================================================================
    // imap_is_open Parity Tests
    // =========================================================================

    public function testImapIsOpenParity(): void
    {
        if (!$this->hasImapIsOpen) {
            Assert::false($this->hasImapIsOpen, 'Native imap_is_open() not available');

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

        $native = imap_is_open($imapStream); // @phpstan-ignore function.notFound
        $polyfill = Php82::imap_is_open($imapStream);

        Assert::same($native, $polyfill);

        imap_close($imapStream);
    }

    // =========================================================================
    // memory_reset_peak_usage Parity Tests
    // =========================================================================

    public function testMemoryResetPeakUsageParity(): void
    {
        if (!$this->hasMemoryResetPeakUsage) {
            Assert::false($this->hasMemoryResetPeakUsage, 'Native memory_reset_peak_usage() not available');

            return;
        }

        memory_reset_peak_usage(); // @phpstan-ignore function.notFound
        Php82::memory_reset_peak_usage();
        Assert::true(true);
    }

    // =========================================================================
    // mysqli_execute_query Parity Tests
    // =========================================================================

    public function testMysqliExecuteQueryParity(): void
    {
        if (!$this->hasMysqliExecuteQuery) {
            Assert::false($this->hasMysqliExecuteQuery, 'Native mysqli_execute_query() not available');

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
            Assert::true($result, 'Failed to create test database');

            return;
        }

        // Select test database
        $result = mysqli_select_db($mysqli, 'continuum_test');
        if ($result === false) {
            Assert::true($result, 'Failed to connect to test database');

            return;
        }

        mysqli_query($mysqli, 'DROP TABLE IF EXISTS parity_test');
        mysqli_query($mysqli, 'CREATE TABLE parity_test (id INT AUTO_INCREMENT PRIMARY KEY, value INT)');

        // Insert test data
        mysqli_query($mysqli, 'INSERT INTO parity_test (value) VALUES (1), (2), (3)');

        // Test with parameters
        $query = 'SELECT * FROM parity_test WHERE value = ?';
        $params = [2];

        try {
            $nativeResult = mysqli_execute_query($mysqli, $query, $params); // @phpstan-ignore function.notFound
            $polyfillResult = Php82::mysqli_execute_query($mysqli, $query, $params);

            $nativeRows = $nativeResult instanceof mysqli_result ? mysqli_fetch_all($nativeResult, MYSQLI_ASSOC) : false;
            $polyfillRows = $polyfillResult instanceof mysqli_result ? mysqli_fetch_all($polyfillResult, MYSQLI_ASSOC) : false;

            Assert::same($nativeRows, $polyfillRows);
        } finally {
            // Clean up
            mysqli_query($mysqli, 'DROP TABLE IF EXISTS parity_test');
            mysqli_query($mysqli, 'DROP DATABASE IF EXISTS continuum_test');
            mysqli_close($mysqli);
        }
    }

    // =========================================================================
    // openssl_cipher_key_length Parity Tests
    // =========================================================================

    public function testOpensslCipherKeyLengthParity(): void
    {
        if (!$this->hasOpensslCipherKeyLength) {
            Assert::false($this->hasOpensslCipherKeyLength, 'Native openssl_cipher_key_length() not available');

            return;
        }

        $ciphers = [
            'AES-128-CBC',
            'AES-256-GCM',
            'DES-CBC',
            'UNKNOWN-CIPHER-XYZ'
        ];

        foreach ($ciphers as $cipher) {
            $native = @openssl_cipher_key_length($cipher); // @phpstan-ignore function.notFound
            $polyfill = @Php82::openssl_cipher_key_length($cipher);

            Assert::same($native, $polyfill, "Parity failed for openssl_cipher_key_length('{$cipher}')");
        }
    }
}

(new Php82ParityTest())->run();
