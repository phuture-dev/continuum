<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use Tester\{Assert, TestCase};
use Phuture\Continuum\Extension\ZipArchive;

require __DIR__ . '/bootstrap.php';

/**
 * Unit tests for the ZipArchive polyfill class.
 *
 * @testCase
 */
class ZipArchiveTest extends TestCase
{
    private string $zipPath;
    private string $extractPath;

    protected function setUp(): void
    {
        $pid = getmypid();
        $this->zipPath = sys_get_temp_dir() . '/continuum_za_' . $pid . '.zip';
        $this->extractPath = sys_get_temp_dir() . '/continuum_za_extract_' . $pid;

        if (!is_dir($this->extractPath)) {
            mkdir($this->extractPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->zipPath)) {
            unlink($this->zipPath);
        }

        $this->rmdir($this->extractPath);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function rmdir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . '/' . $item;
            is_dir($full) ? $this->rmdir($full) : unlink($full);
        }

        rmdir($path);
    }

    private function makeZip(): ZipArchive
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $za->addFromString('hello.txt', 'Hello, World!');
        $za->addFromString('dir/nested.txt', 'Nested file content here');
        $za->addEmptyDir('emptydir');
        $za->close();

        return $za;
    }

    // =========================================================================
    // open() Tests
    // =========================================================================

    public function testOpenCreatesNewArchive(): void
    {
        $za = new ZipArchive();
        $result = $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::true($result);
        $za->addFromString('a.txt', 'a');
        $za->close();
        Assert::true(file_exists($this->zipPath));
    }

    public function testOpenOverwriteExistingArchive(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('old.txt', 'old');
        $za->close();

        $za2 = new ZipArchive();
        $result = $za2->open($this->zipPath, ZipArchive::OVERWRITE);
        Assert::true($result);
        $za2->addFromString('new.txt', 'new');
        $za2->close();

        $za3 = new ZipArchive();
        $za3->open($this->zipPath);
        Assert::false($za3->locateName('old.txt'));
        Assert::same(0, $za3->locateName('new.txt'));
        $za3->close();
    }

    public function testOpenExclFailsIfFileExists(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->close();

        $za2 = new ZipArchive();
        $result = $za2->open($this->zipPath, ZipArchive::EXCL);
        Assert::same(ZipArchive::ER_EXISTS, $result);
    }

    public function testOpenReturnsErrNoentForMissingFile(): void
    {
        $za = new ZipArchive();
        $result = $za->open('/nonexistent/path/archive.zip');
        Assert::same(ZipArchive::ER_NOENT, $result);
    }

    public function testOpenReturnsErrNoZipForInvalidFile(): void
    {
        $invalid = sys_get_temp_dir() . '/not_a_zip_' . getmypid() . '.txt';
        file_put_contents($invalid, 'not a zip');
        $za = new ZipArchive();
        $result = $za->open($invalid);
        Assert::same(ZipArchive::ER_NOZIP, $result);
        unlink($invalid);
    }

    public function testOpenExistingFileWithoutFlags(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $result = $za->open($this->zipPath);
        Assert::true($result);
        Assert::same('Hello, World!', $za->getFromName('hello.txt'));
        $za->close();
    }

    // =========================================================================
    // close() Tests
    // =========================================================================

    public function testCloseSavesArchiveToDisk(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('saved.txt', 'saved content');
        $za->close();

        Assert::true(file_exists($this->zipPath));

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('saved content', $za2->getFromName('saved.txt'));
        $za2->close();
    }

    // =========================================================================
    // count() / numFiles Tests
    // =========================================================================

    public function testCountMatchesNumFiles(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::same($za->numFiles, count($za));
        $za->close();
    }

    public function testNumFilesReflectsAddedEntries(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::same(0, $za->numFiles);
        $za->addFromString('a.txt', 'a');
        Assert::same(1, $za->numFiles);
        $za->addFromString('b.txt', 'b');
        Assert::same(2, $za->numFiles);
        $za->close();
    }

    // =========================================================================
    // addFromString() Tests
    // =========================================================================

    public function testAddFromStringStoresContent(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addFromString('test.txt', 'hello polyfill');
        Assert::true($result);
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('hello polyfill', $za2->getFromName('test.txt'));
        $za2->close();
    }

    public function testAddFromStringUpdatesLastId(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::same(-1, $za->lastId);
        $za->addFromString('a.txt', 'a');
        Assert::same(0, $za->lastId);
        $za->addFromString('b.txt', 'b');
        Assert::same(1, $za->lastId);
        $za->close();
    }

    // =========================================================================
    // addFile() Tests
    // =========================================================================

    public function testAddFileCopiesContentFromDisk(): void
    {
        $src = sys_get_temp_dir() . '/za_src_' . getmypid() . '.txt';
        file_put_contents($src, 'file on disk');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addFile($src, 'fromfile.txt');
        Assert::true($result);
        $za->close();
        unlink($src);

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('file on disk', $za2->getFromName('fromfile.txt'));
        $za2->close();
    }

    public function testAddFileUsesBasenameWhenEntryNameOmitted(): void
    {
        $src = sys_get_temp_dir() . '/za_basename_' . getmypid() . '.txt';
        file_put_contents($src, 'content');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFile($src);
        $za->close();
        unlink($src);

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::type('string', $za2->getFromName(basename($src)));
        $za2->close();
    }

    // =========================================================================
    // addEmptyDir() Tests
    // =========================================================================

    public function testAddEmptyDirCreatesDirectoryEntry(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addEmptyDir('mydir');
        Assert::true($result);
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::truthy($za2->locateName('mydir/') !== false);
        $za2->close();
    }

    // =========================================================================
    // deleteIndex() / deleteName() Tests
    // =========================================================================

    public function testDeleteNameRemovesEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::true($za->deleteName('hello.txt'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::false($za2->getFromName('hello.txt'));
        $za2->close();
    }

    public function testDeleteIndexRemovesEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $name = $za->getNameIndex(0);
        Assert::type('string', $name);
        Assert::true($za->deleteIndex(0));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::false($za2->locateName($name));
        $za2->close();
    }

    public function testDeleteNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->deleteName('nonexistent.txt'));
        $za->close();
    }

    // =========================================================================
    // renameIndex() / renameName() Tests
    // =========================================================================

    public function testRenameNameChangesEntryName(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::true($za->renameName('hello.txt', 'renamed.txt'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('Hello, World!', $za2->getFromName('renamed.txt'));
        Assert::false($za2->getFromName('hello.txt'));
        $za2->close();
    }

    public function testRenameIndexChangesEntryName(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::true($za->renameIndex(0, 'index_renamed.txt'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::truthy($za2->locateName('index_renamed.txt') !== false);
        $za2->close();
    }

    public function testRenameNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->renameName('nonexistent.txt', 'new.txt'));
        $za->close();
    }

    // =========================================================================
    // getFromName() / getFromIndex() Tests
    // =========================================================================

    public function testGetFromNameReturnsCorrectContent(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::same('Hello, World!', $za->getFromName('hello.txt'));
        $za->close();
    }

    public function testGetFromNameWithLengthLimit(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::same('Hello', $za->getFromName('hello.txt', 5));
        $za->close();
    }

    public function testGetFromNameCaseInsensitive(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->getFromName('HELLO.TXT', 0, ZipArchive::FL_NOCASE);
        Assert::same('Hello, World!', $result);
        $za->close();
    }

    public function testGetFromNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->getFromName('nonexistent.txt'));
        $za->close();
    }

    public function testGetFromIndexReturnsCorrectContent(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $content = $za->getFromIndex(0);
        Assert::type('string', $content);
        Assert::truthy(strlen($content) > 0);
        $za->close();
    }

    public function testGetFromIndexReturnsFalseForInvalidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->getFromIndex(999));
        $za->close();
    }

    // =========================================================================
    // statName() / statIndex() Tests
    // =========================================================================

    public function testStatNameReturnsExpectedFields(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $stat = $za->statName('hello.txt');
        Assert::type('array', $stat);
        Assert::same('hello.txt', $stat['name']);
        Assert::type('int', $stat['index']);
        Assert::type('int', $stat['crc']);
        Assert::same(strlen('Hello, World!'), $stat['size']);
        Assert::type('int', $stat['mtime']);
        Assert::type('int', $stat['comp_size']);
        Assert::type('int', $stat['comp_method']);
        Assert::type('int', $stat['encryption_method']);
        $za->close();
    }

    public function testStatNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->statName('nonexistent.txt'));
        $za->close();
    }

    public function testStatIndexReturnsSameAsStatName(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $byName  = $za->statName('hello.txt');
        $byIndex = $za->statIndex($byName['index']);
        Assert::same($byName, $byIndex);
        $za->close();
    }

    // =========================================================================
    // locateName() / getNameIndex() Tests
    // =========================================================================

    public function testLocateNameReturnsCorrectIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $index = $za->locateName('hello.txt');
        Assert::type('int', $index);
        Assert::same('hello.txt', $za->getNameIndex($index));
        $za->close();
    }

    public function testLocateNameCaseInsensitive(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $index = $za->locateName('HELLO.TXT', ZipArchive::FL_NOCASE);
        Assert::truthy($index !== false);
        $za->close();
    }

    public function testLocateNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->locateName('nonexistent.txt'));
        $za->close();
    }

    public function testGetNameIndexReturnsFalseForInvalidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->getNameIndex(999));
        $za->close();
    }

    // =========================================================================
    // Archive Comment Tests
    // =========================================================================

    public function testSetAndGetArchiveComment(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('a.txt', 'a');
        Assert::true($za->setArchiveComment('My archive comment'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('My archive comment', $za2->getArchiveComment());
        $za2->close();
    }

    public function testGetArchiveCommentDefaultIsEmpty(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('a.txt', 'a');
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $comment = $za2->getArchiveComment();
        Assert::truthy($comment === '' || $comment === null);
        $za2->close();
    }

    // =========================================================================
    // Entry Comment Tests
    // =========================================================================

    public function testSetAndGetCommentName(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('a.txt', 'content');
        Assert::true($za->setCommentName('a.txt', 'entry comment'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('entry comment', $za2->getCommentName('a.txt'));
        $za2->close();
    }

    public function testSetAndGetCommentIndex(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('b.txt', 'content');
        Assert::true($za->setCommentIndex(0, 'index comment'));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::same('index comment', $za2->getCommentIndex(0));
        $za2->close();
    }

    public function testGetCommentNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->getCommentName('nonexistent.txt'));
        $za->close();
    }

    // =========================================================================
    // extractTo() Tests
    // =========================================================================

    public function testExtractToExtractsAllFiles(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->extractTo($this->extractPath);
        Assert::true($result);
        $za->close();

        Assert::true(file_exists($this->extractPath . '/hello.txt'));
        Assert::same('Hello, World!', file_get_contents($this->extractPath . '/hello.txt'));
        Assert::true(file_exists($this->extractPath . '/dir/nested.txt'));
    }

    public function testExtractToSelectiveFiles(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->extractTo($this->extractPath, ['hello.txt']);
        Assert::true($result);
        $za->close();

        Assert::true(file_exists($this->extractPath . '/hello.txt'));
        Assert::false(file_exists($this->extractPath . '/dir/nested.txt'));
    }

    public function testExtractToSingleFileAsString(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->extractTo($this->extractPath, 'hello.txt');
        Assert::true($result);
        $za->close();

        Assert::true(file_exists($this->extractPath . '/hello.txt'));
    }

    // =========================================================================
    // setCompressionName() / setCompressionIndex() Tests
    // =========================================================================

    public function testSetCompressionNameWithStore(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('c.txt', 'compress me');
        $result = $za->setCompressionName('c.txt', ZipArchive::CM_STORE);
        Assert::true($result);
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $stat = $za2->statName('c.txt');
        Assert::same(ZipArchive::CM_STORE, $stat['comp_method']);
        $za2->close();
    }

    public function testSetCompressionIndexWithDeflate(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('d.txt', str_repeat('deflate me ', 100));
        $result = $za->setCompressionIndex(0, ZipArchive::CM_DEFLATE);
        Assert::true($result);
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $stat = $za2->statName('d.txt');
        Assert::same(ZipArchive::CM_DEFLATE, $stat['comp_method']);
        $za2->close();
    }

    public function testSetCompressionNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->setCompressionName('nonexistent.txt', ZipArchive::CM_STORE));
        $za->close();
    }

    // =========================================================================
    // isCompressionMethodSupported() Tests
    // =========================================================================

    public function testIsCompressionMethodSupportedForKnownMethods(): void
    {
        Assert::true(ZipArchive::isCompressionMethodSupported(ZipArchive::CM_STORE));
        Assert::true(ZipArchive::isCompressionMethodSupported(ZipArchive::CM_DEFLATE));
    }

    public function testIsCompressionMethodSupportedReturnsFalseForUnknown(): void
    {
        Assert::false(ZipArchive::isCompressionMethodSupported(ZipArchive::CM_LZMA));
    }

    // =========================================================================
    // isEncryptionMethodSupported() Tests
    // =========================================================================

    public function testIsEncryptionMethodSupportedForKnownMethods(): void
    {
        Assert::true(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_NONE));
        Assert::true(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_TRAD_PKWARE));
        Assert::true(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_128));
        Assert::true(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_192));
        Assert::true(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256));
    }

    public function testIsEncryptionMethodSupportedReturnsFalseForUnknown(): void
    {
        Assert::false(ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_UNKNOWN));
    }

    // =========================================================================
    // setMtimeName() / setMtimeIndex() Tests
    // =========================================================================

    public function testSetMtimeNameUpdatesModificationTime(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('mtime.txt', 'data');
        $ts = mktime(12, 0, 0, 6, 15, 2023);
        Assert::true($za->setMtimeName('mtime.txt', $ts));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $stat = $za2->statName('mtime.txt');
        Assert::same($ts, $stat['mtime']);
        $za2->close();
    }

    public function testSetMtimeIndexUpdatesModificationTime(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('mtime2.txt', 'data');
        $ts = mktime(8, 30, 0, 1, 1, 2022);
        Assert::true($za->setMtimeIndex(0, $ts));
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $stat = $za2->statIndex(0);
        Assert::same($ts, $stat['mtime']);
        $za2->close();
    }

    public function testSetMtimeNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->setMtimeName('nonexistent.txt', time()));
        $za->close();
    }

    // =========================================================================
    // unchangeAll() / unchangeName() Tests
    // =========================================================================

    public function testUnchangeAllRevertsPendingChanges(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->addFromString('pending.txt', 'pending');
        Assert::true($za->unchangeAll());
        $za->close();

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        Assert::false($za2->locateName('pending.txt'));
        $za2->close();
    }

    public function testUnchangeNameRevertsEntryChanges(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->setCommentName('hello.txt', 'temporary comment');
        Assert::true($za->unchangeName('hello.txt'));
        $za->close();
    }

    // =========================================================================
    // getStream() / getStreamIndex() Tests
    // =========================================================================

    public function testGetStreamReturnsReadableResource(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $stream = $za->getStream('hello.txt');
        Assert::type('resource', $stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        Assert::same('Hello, World!', $content);
        $za->close();
    }

    public function testGetStreamReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        Assert::false($za->getStream('nonexistent.txt'));
        $za->close();
    }

    public function testGetStreamIndexReturnsReadableResource(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $index = $za->locateName('hello.txt');
        $stream = $za->getStreamIndex($index);
        Assert::type('resource', $stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        Assert::same('Hello, World!', $content);
        $za->close();
    }

    // =========================================================================
    // getStatusString() / clearError() / status Tests
    // =========================================================================

    public function testStatusIsZeroAfterSuccessfulOpen(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::same(ZipArchive::ER_OK, $za->status);
        $za->close();
    }

    public function testGetStatusStringReturnsString(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::type('string', $za->getStatusString());
        $za->close();
    }

    public function testClearErrorResetsStatus(): void
    {
        $za = new ZipArchive();
        $za->open('/nonexistent/file.zip');
        Assert::notSame(ZipArchive::ER_OK, $za->status);
        $za->clearError();
        Assert::same(ZipArchive::ER_OK, $za->status);
        Assert::same(0, $za->statusSys);
    }

    // =========================================================================
    // Unsupported methods Tests
    // =========================================================================

    public function testUnsupportedMethodsReturnFalseOrZero(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('x.txt', 'x');

        Assert::false($za->registerProgressCallback(0.1, static function (): void {
        }));
        Assert::false($za->registerCancelCallback(static function (): int {
            return 0;
        }));
        Assert::same(0, $za->getArchiveFlag(ZipArchive::AFL_RDONLY));
        Assert::false($za->setArchiveFlag(ZipArchive::AFL_RDONLY, 1));

        $za->close();
    }

    // =========================================================================
    // Constants Tests
    // =========================================================================

    public function testConstantValuesMatchNativeSpec(): void
    {
        Assert::same(1, ZipArchive::CREATE);
        Assert::same(2, ZipArchive::EXCL);
        Assert::same(4, ZipArchive::CHECKCONS);
        Assert::same(8, ZipArchive::OVERWRITE);
        Assert::same(16, ZipArchive::RDONLY);
        Assert::same(0, ZipArchive::ER_OK);
        Assert::same(9, ZipArchive::ER_NOENT);
        Assert::same(10, ZipArchive::ER_EXISTS);
        Assert::same(19, ZipArchive::ER_NOZIP);
        Assert::same(0, ZipArchive::CM_STORE);
        Assert::same(8, ZipArchive::CM_DEFLATE);
        Assert::same(-1, ZipArchive::CM_DEFAULT);
        Assert::same(0, ZipArchive::EM_NONE);
        Assert::same(1, ZipArchive::EM_TRAD_PKWARE);
        Assert::same(259, ZipArchive::EM_AES_256);
    }

    // =========================================================================
    // filename / comment public property Tests
    // =========================================================================

    public function testFilenamePropertySetAfterOpen(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        Assert::same(realpath($this->zipPath) ?: $this->zipPath, $za->filename);
        $za->close();
    }

    public function testCommentPropertyReflectsArchiveComment(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('a.txt', 'a');
        $za->setArchiveComment('test comment');
        Assert::same('test comment', $za->comment);
        $za->close();
    }

    // =========================================================================
    // replaceFile() Tests
    // =========================================================================

    public function testReplaceFileReplacesEntryContent(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('replace_me.txt', 'old content');
        $za->close();

        $src = sys_get_temp_dir() . '/za_replace_' . getmypid() . '.txt';
        file_put_contents($src, 'new content');

        $za2 = new ZipArchive();
        $za2->open($this->zipPath);
        $index = $za2->locateName('replace_me.txt');
        $result = $za2->replaceFile($src, $index);
        Assert::true($result);
        $za2->close();
        unlink($src);

        $za3 = new ZipArchive();
        $za3->open($this->zipPath);
        Assert::same('new content', $za3->getFromName('replace_me.txt'));
        $za3->close();
    }

    // =========================================================================
    // External Attributes Tests
    // =========================================================================

    public function testSetAndGetExternalAttributesName(): void
    {
        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $za->addFromString('attrs.txt', 'data');
        $result = $za->setExternalAttributesName('attrs.txt', ZipArchive::OPSYS_UNIX, 0644 << 16);
        Assert::true($result);

        $opsys = 0;
        $attr  = 0;
        Assert::true($za->getExternalAttributesName('attrs.txt', $opsys, $attr));
        Assert::same(ZipArchive::OPSYS_UNIX, $opsys);
        $za->close();
    }

    public function testGetExternalAttributesIndexReturnsFalseForInvalidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $opsys = 0;
        $attr  = 0;
        Assert::false($za->getExternalAttributesIndex(999, $opsys, $attr));
        $za->close();
    }

    public function testSetExternalAttributesIndexReturnsTrueForValidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->setExternalAttributesIndex(0, ZipArchive::OPSYS_UNIX, 0644 << 16);
        $za->close();
        Assert::true($result);
    }

    public function testSetAndGetExternalAttributesIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->setExternalAttributesIndex(0, ZipArchive::OPSYS_UNIX, 0644 << 16);
        $opsys = 0;
        $attr  = 0;
        $result = $za->getExternalAttributesIndex(0, $opsys, $attr);
        $za->close();
        Assert::true($result);
        Assert::same(ZipArchive::OPSYS_UNIX, $opsys);
        Assert::same(0644 << 16, $attr);
    }

    // =========================================================================
    // addGlob() Tests
    // =========================================================================

    public function testAddGlobReturnsArrayOfAddedEntries(): void
    {
        $tmpDir = sys_get_temp_dir() . '/continuum_glob_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/a.txt', 'file a');
        file_put_contents($tmpDir . '/b.txt', 'file b');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addGlob($tmpDir . '/*.txt');
        $za->close();
        $this->rmdir($tmpDir);

        Assert::type('array', $result);
        Assert::count(2, $result);
    }

    public function testAddGlobWithRemoveAllPath(): void
    {
        $tmpDir = sys_get_temp_dir() . '/continuum_glob2_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/c.txt', 'file c');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addGlob($tmpDir . '/*.txt', 0, ['remove_all_path' => true]);
        $za->close();
        $this->rmdir($tmpDir);

        Assert::same(['c.txt'], $result);
    }

    public function testAddGlobWithAddPath(): void
    {
        $tmpDir = sys_get_temp_dir() . '/continuum_glob3_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/d.txt', 'file d');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addGlob($tmpDir . '/*.txt', 0, ['remove_all_path' => true, 'add_path' => 'prefix/']);
        $za->close();
        $this->rmdir($tmpDir);

        Assert::same(['prefix/d.txt'], $result);
    }

    // =========================================================================
    // addPattern() Tests
    // =========================================================================

    public function testAddPatternAddsMatchingFiles(): void
    {
        $tmpDir = sys_get_temp_dir() . '/continuum_patt_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/match.txt', 'txt content');
        file_put_contents($tmpDir . '/skip.php', '<?php');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addPattern('/\.txt$/', $tmpDir, ['remove_all_path' => true]);
        $za->close();
        $this->rmdir($tmpDir);

        Assert::type('array', $result);
        Assert::same(['match.txt'], $result);
    }

    public function testAddPatternReturnsFalseWhenNoMatches(): void
    {
        $tmpDir = sys_get_temp_dir() . '/continuum_patt2_' . getmypid();
        mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '/skip.php', '<?php');

        $za = new ZipArchive();
        $za->open($this->zipPath, ZipArchive::CREATE);
        $result = $za->addPattern('/\.txt$/', $tmpDir);
        $za->close();
        $this->rmdir($tmpDir);

        Assert::false($result);
    }

    // =========================================================================
    // getStreamName() Tests
    // =========================================================================

    public function testGetStreamNameReturnsReadableResource(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $stream = $za->getStreamName('hello.txt');
        Assert::notSame(false, $stream);
        $content = stream_get_contents($stream);
        $za->close();
        Assert::same('Hello, World!', $content);
    }

    public function testGetStreamNameReturnsFalseForMissingEntry(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->getStreamName('nonexistent.txt');
        $za->close();
        Assert::false($result);
    }

    // =========================================================================
    // setEncryptionName() Tests
    // =========================================================================

    public function testSetEncryptionNameReturnsTrueWithPassword(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->setPassword('secret');
        $result = $za->setEncryptionName('hello.txt', ZipArchive::EM_AES_256);
        $za->close();
        Assert::true($result);
    }

    public function testSetEncryptionNameReturnsFalseWithoutPassword(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->setEncryptionName('hello.txt', ZipArchive::EM_AES_256);
        $za->close();
        Assert::false($result);
    }

    // =========================================================================
    // setEncryptionIndex() Tests
    // =========================================================================

    public function testSetEncryptionIndexReturnsTrueForValidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->setPassword('secret');
        $result = $za->setEncryptionIndex(0, ZipArchive::EM_AES_256);
        $za->close();
        Assert::true($result);
    }

    public function testSetEncryptionIndexReturnsFalseForInvalidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $za->setPassword('secret');
        $result = $za->setEncryptionIndex(999, ZipArchive::EM_AES_256);
        $za->close();
        Assert::false($result);
    }

    // =========================================================================
    // unchangeIndex() Tests
    // =========================================================================

    public function testUnchangeIndexReturnsTrueForValidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->unchangeIndex(0);
        $za->close();
        Assert::true($result);
    }

    public function testUnchangeIndexReturnsFalseForInvalidIndex(): void
    {
        $this->makeZip();
        $za = new ZipArchive();
        $za->open($this->zipPath);
        $result = $za->unchangeIndex(999);
        $za->close();
        Assert::false($result);
    }
}

(new ZipArchiveTest())->run();
