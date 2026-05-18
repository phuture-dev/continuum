<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use ZipArchive;
use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\Zip;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for Zip polyfill methods.
 *
 * @testCase
 */
class ZipTest extends TestCase
{
    private string $zipPath;

    protected function setUp(): void
    {
        $this->zipPath = sys_get_temp_dir() . '/continuum_zip_' . getmypid() . '.zip';

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
    // zip_open Tests
    // =========================================================================

    public function testZipOpenReturnsHandleForValidFile(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        Assert::type('object', $archive);
        Zip::zip_close($archive);
    }

    public function testZipOpenReturnsFalseForMissingFile(): void
    {
        $result = Zip::zip_open('/nonexistent/path/archive.zip');
        Assert::false($result);
    }

    public function testZipOpenReturnsFalseForInvalidZip(): void
    {
        $invalidPath = sys_get_temp_dir() . '/not_a_zip.txt';
        file_put_contents($invalidPath, 'this is not a zip file');
        $result = Zip::zip_open($invalidPath);
        Assert::false($result);
        unlink($invalidPath);
    }

    // =========================================================================
    // zip_close Tests
    // =========================================================================

    public function testZipCloseReleasesHandle(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        Assert::type('object', $archive);
        Zip::zip_close($archive);
        // Calling close a second time should be a no-op (no crash)
        Zip::zip_close($archive);
    }

    public function testZipCloseWithInvalidHandleIsNoOp(): void
    {
        Zip::zip_close(false);
        Zip::zip_close(null);
        Zip::zip_close('string');
        // No exception expected
        Assert::true(true);
    }

    // =========================================================================
    // zip_read Tests
    // =========================================================================

    public function testZipReadIteratesAllEntries(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        Assert::type('object', $archive);

        $names = [];
        while ($entry = Zip::zip_read($archive)) {
            $names[] = Zip::zip_entry_name($entry);
        }

        Zip::zip_close($archive);

        Assert::contains('hello.txt', $names);
        Assert::contains('dir/nested.txt', $names);
        Assert::contains('emptydir/', $names);
    }

    public function testZipReadReturnsFalseAtEndOfArchive(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while (Zip::zip_read($archive)) {
        }

        $result = Zip::zip_read($archive);
        Assert::false($result);
        Zip::zip_close($archive);
    }

    public function testZipReadReturnsFalseForInvalidHandle(): void
    {
        Assert::false(Zip::zip_read(false));
        Assert::false(Zip::zip_read(null));
        Assert::false(Zip::zip_read('bad'));
    }

    // =========================================================================
    // zip_entry_open / zip_entry_close Tests
    // =========================================================================

    public function testZipEntryOpenReturnsTrueForValidEntry(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);
        Assert::true(Zip::zip_entry_open($archive, $entry));
        Zip::zip_entry_close($entry);
        Zip::zip_close($archive);
    }

    public function testZipEntryOpenReturnsFalseForInvalidHandles(): void
    {
        Assert::false(Zip::zip_entry_open(false, false));
        Assert::false(Zip::zip_entry_open(null, null));
    }

    public function testZipEntryCloseReturnsTrueForOpenEntry(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);
        Zip::zip_entry_open($archive, $entry);
        Assert::true(Zip::zip_entry_close($entry));
        Zip::zip_close($archive);
    }

    public function testZipEntryCloseReturnsFalseForInvalidHandle(): void
    {
        Assert::false(Zip::zip_entry_close(false));
        Assert::false(Zip::zip_entry_close(null));
        Assert::false(Zip::zip_entry_close('bad'));
    }

    // =========================================================================
    // zip_entry_read Tests
    // =========================================================================

    public function testZipEntryReadReturnsFullContentInOneChunk(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while ($entry = Zip::zip_read($archive)) {
            if (Zip::zip_entry_name($entry) === 'hello.txt') {
                Zip::zip_entry_open($archive, $entry);
                $content = Zip::zip_entry_read($entry, 1024);
                Zip::zip_entry_close($entry);
                Assert::same('Hello, World!', $content);
                break;
            }
        }

        Zip::zip_close($archive);
    }

    public function testZipEntryReadReturnsContentInChunks(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while ($entry = Zip::zip_read($archive)) {
            if (Zip::zip_entry_name($entry) === 'dir/nested.txt') {
                Zip::zip_entry_open($archive, $entry);

                $chunks = [];
                while (($chunk = Zip::zip_entry_read($entry, 4)) !== '') {
                    $chunks[] = $chunk;
                }

                Zip::zip_entry_close($entry);
                Assert::same('Nested file content here', implode('', $chunks));
                break;
            }
        }

        Zip::zip_close($archive);
    }

    public function testZipEntryReadReturnsEmptyStringAtEof(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);
        Zip::zip_entry_open($archive, $entry);

        // Read the whole entry first
        while (Zip::zip_entry_read($entry, 1024) !== '') {
        }

        // Next read should return empty string (EOF)
        Assert::same('', Zip::zip_entry_read($entry));

        Zip::zip_entry_close($entry);
        Zip::zip_close($archive);
    }

    public function testZipEntryReadReturnsFalseWhenNotOpened(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);

        Assert::false(Zip::zip_entry_read($entry));

        Zip::zip_close($archive);
    }

    public function testZipEntryReadReturnsFalseForInvalidHandle(): void
    {
        Assert::false(Zip::zip_entry_read(false));
        Assert::false(Zip::zip_entry_read(null));
        Assert::false(Zip::zip_entry_read('bad'));
    }

    public function testZipEntryReadEmptyForDirectoryEntry(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while ($entry = Zip::zip_read($archive)) {
            if (Zip::zip_entry_name($entry) === 'emptydir/') {
                Zip::zip_entry_open($archive, $entry);
                $content = Zip::zip_entry_read($entry, 1024);
                Zip::zip_entry_close($entry);
                Assert::same('', $content);
                break;
            }
        }

        Zip::zip_close($archive);
    }

    // =========================================================================
    // zip_entry_name Tests
    // =========================================================================

    public function testZipEntryNameReturnsCorrectName(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);

        $name = Zip::zip_entry_name($entry);
        Assert::type('string', $name);
        Assert::truthy($name);

        Zip::zip_close($archive);
    }

    public function testZipEntryNameReturnsEmptyStringForInvalidHandle(): void
    {
        Assert::same('', Zip::zip_entry_name(false));
        Assert::same('', Zip::zip_entry_name(null));
        Assert::same('', Zip::zip_entry_name('bad'));
    }

    // =========================================================================
    // zip_entry_filesize Tests
    // =========================================================================

    public function testZipEntryFilesizeReturnsCorrectSize(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while ($entry = Zip::zip_read($archive)) {
            if (Zip::zip_entry_name($entry) === 'hello.txt') {
                Assert::same(strlen('Hello, World!'), Zip::zip_entry_filesize($entry));
                break;
            }
        }

        Zip::zip_close($archive);
    }

    public function testZipEntryFilesizeReturnsZeroForInvalidHandle(): void
    {
        Assert::same(0, Zip::zip_entry_filesize(false));
        Assert::same(0, Zip::zip_entry_filesize(null));
        Assert::same(0, Zip::zip_entry_filesize('bad'));
    }

    // =========================================================================
    // zip_entry_compressedsize Tests
    // =========================================================================

    public function testZipEntryCompressedSizeReturnsInteger(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);

        Assert::type('int', Zip::zip_entry_compressedsize($entry));

        Zip::zip_close($archive);
    }

    public function testZipEntryCompressedSizeReturnsZeroForInvalidHandle(): void
    {
        Assert::same(0, Zip::zip_entry_compressedsize(false));
        Assert::same(0, Zip::zip_entry_compressedsize(null));
        Assert::same(0, Zip::zip_entry_compressedsize('bad'));
    }

    public function testZipEntryCompressedSizeNotGreaterThanFilesizeForStoredEntries(): void
    {
        $archive = Zip::zip_open($this->zipPath);

        while ($entry = Zip::zip_read($archive)) {
            $compressed = Zip::zip_entry_compressedsize($entry);
            $uncompressed = Zip::zip_entry_filesize($entry);

            if ($uncompressed > 0) {
                Assert::truthy($compressed <= $uncompressed);
            }
        }

        Zip::zip_close($archive);
    }

    // =========================================================================
    // zip_entry_compressionmethod Tests
    // =========================================================================

    public function testZipEntryCompressionMethodReturnsString(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);

        $method = Zip::zip_entry_compressionmethod($entry);
        Assert::type('string', $method);
        Assert::truthy($method);

        Zip::zip_close($archive);
    }

    public function testZipEntryCompressionMethodIsLowercase(): void
    {
        $archive = Zip::zip_open($this->zipPath);
        $entry = Zip::zip_read($archive);

        $method = Zip::zip_entry_compressionmethod($entry);
        Assert::same(strtolower($method), $method);

        Zip::zip_close($archive);
    }

    public function testZipEntryCompressionMethodReturnsEmptyStringForInvalidHandle(): void
    {
        Assert::same('', Zip::zip_entry_compressionmethod(false));
        Assert::same('', Zip::zip_entry_compressionmethod(null));
        Assert::same('', Zip::zip_entry_compressionmethod('bad'));
    }
}

(new ZipTest())->run();
