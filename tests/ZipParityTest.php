<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use ZipArchive;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Zip;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for the Zip polyfill against native zip_* functions.
 *
 * Each test runs the same operation through both the native zip_* functions and the
 * Zip polyfill, then asserts that the observable results are identical. Tests are
 * skipped automatically when the native zip_open() function is not available.
 *
 * @testCase
 */
class ZipParityTest extends TestCase
{
    private string $zipPath;

    protected function setUp(): void
    {
        if (!function_exists('zip_open')) {
            \Tester\Environment::skip('Native zip_open() not available.');
        }

        $this->zipPath = sys_get_temp_dir() . '/continuum_zipparity_' . getmypid() . '.zip';

        $archive = new ZipArchive();
        $archive->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $archive->addFromString('hello.txt', 'Hello, World!');
        $archive->addFromString('dir/nested.txt', 'Nested file content here');
        $archive->addEmptyDir('emptydir');
        $archive->close();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->zipPath)) {
            unlink($this->zipPath);
        }
    }

    // =========================================================================
    // zip_open Parity Tests
    // =========================================================================

    public function testZipOpenValidFileParity(): void
    {
        $native = @zip_open($this->zipPath);
        $polyfill = Zip::zip_open($this->zipPath);

        Assert::true(is_resource($native));
        Assert::type('object', $polyfill);

        @zip_close($native);
        Zip::zip_close($polyfill);
    }

    public function testZipOpenInvalidFileParity(): void
    {
        $native = @zip_open('/nonexistent/path.zip');
        $polyfill = Zip::zip_open('/nonexistent/path.zip');

        Assert::false(is_resource($native));
        Assert::false($polyfill);
    }

    // =========================================================================
    // zip_close Parity Tests
    // =========================================================================

    public function testZipCloseParity(): void
    {
        $native = @zip_open($this->zipPath);
        $polyfill = Zip::zip_open($this->zipPath);

        // Neither throws — both release the handle cleanly.
        @zip_close($native);
        Zip::zip_close($polyfill);

        Assert::true(true);
    }

    // =========================================================================
    // zip_read Parity Tests
    // =========================================================================

    public function testZipReadEntryOrderParity(): void
    {
        Assert::same(
            $this->collectNativeEntryNames(),
            $this->collectPolyfillEntryNames()
        );
    }

    public function testZipReadReturnsFalseAtEndParity(): void
    {
        $nativeArchive = @zip_open($this->zipPath);
        while (@zip_read($nativeArchive)) {
        }
        $nativeResult = @zip_read($nativeArchive);
        @zip_close($nativeArchive);

        $polyfillArchive = Zip::zip_open($this->zipPath);
        while (Zip::zip_read($polyfillArchive)) {
        }
        $polyfillResult = Zip::zip_read($polyfillArchive);
        Zip::zip_close($polyfillArchive);

        Assert::false($nativeResult);
        Assert::false($polyfillResult);
    }

    // =========================================================================
    // zip_entry_open Parity Tests
    // =========================================================================

    public function testZipEntryOpenReturnsParity(): void
    {
        $nativeArchive = @zip_open($this->zipPath);
        $nativeEntry = @zip_read($nativeArchive);
        $nativeResult = (bool)@zip_entry_open($nativeArchive, $nativeEntry);
        @zip_entry_close($nativeEntry);
        @zip_close($nativeArchive);

        $polyfillArchive = Zip::zip_open($this->zipPath);
        $polyfillEntry = Zip::zip_read($polyfillArchive);
        $polyfillResult = Zip::zip_entry_open($polyfillArchive, $polyfillEntry);
        Zip::zip_entry_close($polyfillEntry);
        Zip::zip_close($polyfillArchive);

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // zip_entry_close Parity Tests
    // =========================================================================

    public function testZipEntryCloseReturnsParity(): void
    {
        $nativeArchive = @zip_open($this->zipPath);
        $nativeEntry = @zip_read($nativeArchive);
        @zip_entry_open($nativeArchive, $nativeEntry);
        $nativeResult = (bool)@zip_entry_close($nativeEntry);
        @zip_close($nativeArchive);

        $polyfillArchive = Zip::zip_open($this->zipPath);
        $polyfillEntry = Zip::zip_read($polyfillArchive);
        Zip::zip_entry_open($polyfillArchive, $polyfillEntry);
        $polyfillResult = Zip::zip_entry_close($polyfillEntry);
        Zip::zip_close($polyfillArchive);

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // zip_entry_name Parity Tests
    // =========================================================================

    public function testZipEntryNameParity(): void
    {
        $native = $this->collectNativeData();
        $polyfill = $this->collectPolyfillData();

        foreach ($native as $name => $unused) {
            Assert::same($name, $polyfill[$name]['name'] ?? '');
        }
    }

    // =========================================================================
    // zip_entry_filesize Parity Tests
    // =========================================================================

    public function testZipEntryFilesizeParity(): void
    {
        $native = $this->collectNativeData();
        $polyfill = $this->collectPolyfillData();

        foreach ($native as $name => $data) {
            Assert::same($data['filesize'], $polyfill[$name]['filesize']);
        }
    }

    // =========================================================================
    // zip_entry_compressedsize Parity Tests
    // =========================================================================

    public function testZipEntryCompressedsizeParity(): void
    {
        $native = $this->collectNativeData();
        $polyfill = $this->collectPolyfillData();

        foreach ($native as $name => $data) {
            Assert::same($data['compressedsize'], $polyfill[$name]['compressedsize']);
        }
    }

    // =========================================================================
    // zip_entry_compressionmethod Parity Tests
    // =========================================================================

    public function testZipEntryCompressionmethodParity(): void
    {
        $native = $this->collectNativeData();
        $polyfill = $this->collectPolyfillData();

        foreach ($native as $name => $data) {
            Assert::same($data['compressionmethod'], $polyfill[$name]['compressionmethod']);
        }
    }

    // =========================================================================
    // zip_entry_read Parity Tests
    // =========================================================================

    public function testZipEntryReadContentParity(): void
    {
        $native = $this->collectNativeData();
        $polyfill = $this->collectPolyfillData();

        foreach ($native as $name => $data) {
            Assert::same($data['content'], $polyfill[$name]['content']);
        }
    }

    private function collectNativeData(): array
    {
        $archive = @zip_open($this->zipPath);
        $entries = [];

        while ($entry = @zip_read($archive)) {
            $name = (string)@zip_entry_name($entry);
            @zip_entry_open($archive, $entry);
            $entries[$name] = [
                'name' => $name,
                'filesize' => (int)@zip_entry_filesize($entry),
                'compressedsize' => (int)@zip_entry_compressedsize($entry),
                'compressionmethod' => (string)@zip_entry_compressionmethod($entry),
                'content' => (string)@zip_entry_read($entry, 65536),
            ];
            @zip_entry_close($entry);
        }

        @zip_close($archive);

        return $entries;
    }

    private function collectPolyfillData(): array
    {
        $archive = Zip::zip_open($this->zipPath);
        $entries = [];

        while ($entry = Zip::zip_read($archive)) {
            $name = Zip::zip_entry_name($entry);
            Zip::zip_entry_open($archive, $entry);
            $entries[$name] = [
                'name' => $name,
                'filesize' => Zip::zip_entry_filesize($entry),
                'compressedsize' => Zip::zip_entry_compressedsize($entry),
                'compressionmethod' => Zip::zip_entry_compressionmethod($entry),
                'content' => Zip::zip_entry_read($entry, 65536),
            ];
            Zip::zip_entry_close($entry);
        }

        Zip::zip_close($archive);

        return $entries;
    }

    private function collectNativeEntryNames(): array
    {
        $archive = @zip_open($this->zipPath);
        $names = [];

        while ($entry = @zip_read($archive)) {
            $names[] = (string)@zip_entry_name($entry);
        }

        @zip_close($archive);

        return $names;
    }

    private function collectPolyfillEntryNames(): array
    {
        $archive = Zip::zip_open($this->zipPath);
        $names = [];

        while ($entry = Zip::zip_read($archive)) {
            $names[] = Zip::zip_entry_name($entry);
        }

        Zip::zip_close($archive);

        return $names;
    }
}

(new ZipParityTest())->run();
