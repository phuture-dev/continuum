<?php

declare(strict_types=1);

namespace Phuture\Continuum\Tests;

use ZipArchive;
use FilesystemIterator;
use RecursiveIteratorIterator;
use Tester\{Assert, TestCase};
use RecursiveDirectoryIterator;
use Phuture\Continuum\Extension\ZipArchive as ZipArchivePf;

require __DIR__ . '/bootstrap.php';

/**
 * Parity tests for the ZipArchive polyfill against the native ZipArchive class.
 *
 * Each test runs the same operation through both the native ZipArchive (ext-zip) and
 * Phuture\Continuum\Extension\ZipArchive (polyfill), then asserts that observable results
 * are identical. Tests are skipped automatically when ext-zip is not loaded.
 *
 * @testCase
 */
class ZipArchiveParityTest extends TestCase
{
    private string $sourcePath;
    private string $nativePath;
    private string $polyfillPath;

    protected function setUp(): void
    {
        $pid = getmypid();
        $tmp = sys_get_temp_dir();
        $this->sourcePath = "$tmp/continuum_zaparity_source_$pid.zip";
        $this->nativePath = "$tmp/continuum_zaparity_native_$pid.zip";
        $this->polyfillPath = "$tmp/continuum_zaparity_polyfill_$pid.zip";

        if (!extension_loaded('zip')) {
            \Tester\Environment::skip('ext-zip not available; no native ZipArchive to compare against.');
        }

        $za = new ZipArchive();
        $za->open($this->sourcePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $za->addFromString('hello.txt', 'Hello, World!');
        $za->addFromString('dir/nested.txt', 'Nested file content here');
        $za->addEmptyDir('emptydir');
        $za->close();
    }

    protected function tearDown(): void
    {
        foreach ([$this->sourcePath, $this->nativePath, $this->polyfillPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    // =========================================================================
    // open() Parity Tests
    // =========================================================================

    public function testOpenValidFileParity(): void
    {
        $native = new ZipArchive();
        $polyfill = new ZipArchivePf();

        Assert::same($native->open($this->sourcePath), $polyfill->open($this->sourcePath));

        $native->close();
        $polyfill->close();
    }

    public function testOpenMissingFileParity(): void
    {
        $native = new ZipArchive();
        $polyfill = new ZipArchivePf();

        Assert::same(
            $native->open('/nonexistent/path.zip'),
            $polyfill->open('/nonexistent/path.zip')
        );
    }

    public function testOpenInvalidZipParity(): void
    {
        $path = sys_get_temp_dir() . '/continuum_zaparity_invalid_' . getmypid() . '.txt';
        file_put_contents($path, 'not a zip file');

        $native = new ZipArchive();
        $polyfill = new ZipArchivePf();

        Assert::same($native->open($path), $polyfill->open($path));

        unlink($path);
    }

    public function testOpenCreateFlagParity(): void
    {
        $native = new ZipArchive();
        $polyfill = new ZipArchivePf();

        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        Assert::same($native->open($this->nativePath, $flags), $polyfill->open($this->polyfillPath, $flags));

        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // close() Parity Tests
    // =========================================================================

    public function testCloseParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);

        Assert::same($native->close(), $polyfill->close());
    }

    // =========================================================================
    // count() Parity Tests
    // =========================================================================

    public function testCountParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);

        Assert::same($native->count(), $polyfill->count());
        Assert::same($native->numFiles, $polyfill->numFiles);

        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // addFromString() Parity Tests
    // =========================================================================

    public function testAddFromStringParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $nativeResult = $native->addFromString('test.txt', 'Hello!');
        $nativeCount = $native->count();
        $nativeLastId = $native->lastId;
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfillResult = $polyfill->addFromString('test.txt', 'Hello!');
        $polyfillCount = $polyfill->count();
        $polyfillLastId = $polyfill->lastId;
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
        Assert::same($nativeCount, $polyfillCount);
        Assert::same($nativeLastId, $polyfillLastId);
    }

    // =========================================================================
    // addFile() Parity Tests
    // =========================================================================

    public function testAddFileParity(): void
    {
        $tmp = sys_get_temp_dir() . '/continuum_zaparity_src_' . getmypid() . '.txt';
        file_put_contents($tmp, 'File content');

        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $nativeResult = $native->addFile($tmp, 'file.txt');
        $nativeCount = $native->count();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfillResult = $polyfill->addFile($tmp, 'file.txt');
        $polyfillCount = $polyfill->count();
        $polyfill->close();

        unlink($tmp);

        Assert::same($nativeResult, $polyfillResult);
        Assert::same($nativeCount, $polyfillCount);
    }

    // =========================================================================
    // addEmptyDir() Parity Tests
    // =========================================================================

    public function testAddEmptyDirParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $nativeResult = $native->addEmptyDir('mydir');
        $nativeCount = $native->count();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfillResult = $polyfill->addEmptyDir('mydir');
        $polyfillCount = $polyfill->count();
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
        Assert::same($nativeCount, $polyfillCount);
    }

    // =========================================================================
    // getFromName() Parity Tests
    // =========================================================================

    public function testGetFromNameParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getFromName('hello.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getFromName('hello.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testGetFromNameMissingParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getFromName('nonexistent.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getFromName('nonexistent.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testGetFromNameCaseInsensitiveParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getFromName('HELLO.TXT', 0, ZipArchive::FL_NOCASE);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getFromName('HELLO.TXT', 0, ZipArchivePf::FL_NOCASE);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // getFromIndex() Parity Tests
    // =========================================================================

    public function testGetFromIndexParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getFromIndex(0);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getFromIndex(0);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testGetFromIndexOutOfRangeParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getFromIndex(999);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getFromIndex(999);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // getNameIndex() Parity Tests
    // =========================================================================

    public function testGetNameIndexParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getNameIndex(0);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getNameIndex(0);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testGetNameIndexOutOfRangeParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getNameIndex(999);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getNameIndex(999);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // locateName() Parity Tests
    // =========================================================================

    public function testLocateNameParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->locateName('hello.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->locateName('hello.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testLocateNameMissingParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->locateName('nonexistent.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->locateName('nonexistent.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testLocateNameCaseInsensitiveParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->locateName('HELLO.TXT', ZipArchive::FL_NOCASE);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->locateName('HELLO.TXT', ZipArchivePf::FL_NOCASE);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // statName() Parity Tests
    // =========================================================================

    public function testStatNameParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeStat = $native->statName('hello.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillStat = $polyfill->statName('hello.txt');
        $polyfill->close();

        Assert::type('array', $nativeStat);
        Assert::type('array', $polyfillStat);
        Assert::same($nativeStat['name'], $polyfillStat['name']);
        Assert::same($nativeStat['index'], $polyfillStat['index']);
        Assert::same($nativeStat['size'], $polyfillStat['size']);
        Assert::same($nativeStat['crc'], $polyfillStat['crc']);
    }

    public function testStatNameMissingParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->statName('nonexistent.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->statName('nonexistent.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // statIndex() Parity Tests
    // =========================================================================

    public function testStatIndexParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeStat = $native->statIndex(0);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillStat = $polyfill->statIndex(0);
        $polyfill->close();

        Assert::type('array', $nativeStat);
        Assert::type('array', $polyfillStat);
        Assert::same($nativeStat['name'], $polyfillStat['name']);
        Assert::same($nativeStat['index'], $polyfillStat['index']);
        Assert::same($nativeStat['size'], $polyfillStat['size']);
        Assert::same($nativeStat['crc'], $polyfillStat['crc']);
    }

    public function testStatIndexOutOfRangeParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->statIndex(999);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->statIndex(999);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // deleteName() Parity Tests
    // =========================================================================

    public function testDeleteNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('a.txt', 'A');
        $native->addFromString('b.txt', 'B');
        $nativeResult = $native->deleteName('a.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('a.txt', 'A');
        $polyfill->addFromString('b.txt', 'B');
        $polyfillResult = $polyfill->deleteName('a.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);

        // Compare count after close/re-open — both implementations agree here.
        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);
        Assert::same($native->count(), $polyfill->count());
        Assert::false((bool)$native->locateName('a.txt'));
        Assert::false((bool)$polyfill->locateName('a.txt'));
        $native->close();
        $polyfill->close();
    }

    public function testDeleteNameMissingParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->deleteName('nonexistent.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->deleteName('nonexistent.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // deleteIndex() Parity Tests
    // =========================================================================

    public function testDeleteIndexParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('a.txt', 'A');
        $native->addFromString('b.txt', 'B');
        $nativeResult = $native->deleteIndex(0);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('a.txt', 'A');
        $polyfill->addFromString('b.txt', 'B');
        $polyfillResult = $polyfill->deleteIndex(0);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);

        // Compare count after close/re-open — both implementations agree here.
        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);
        Assert::same($native->count(), $polyfill->count());
        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // renameName() Parity Tests
    // =========================================================================

    public function testRenameNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('original.txt', 'Content');
        $nativeResult = $native->renameName('original.txt', 'renamed.txt');
        $nativeNewName = $native->getNameIndex(0);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('original.txt', 'Content');
        $polyfillResult = $polyfill->renameName('original.txt', 'renamed.txt');
        $polyfillNewName = $polyfill->getNameIndex(0);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
        Assert::same($nativeNewName, $polyfillNewName);
    }

    public function testRenameNameMissingParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->renameName('nonexistent.txt', 'other.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->renameName('nonexistent.txt', 'other.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // renameIndex() Parity Tests
    // =========================================================================

    public function testRenameIndexParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('original.txt', 'Content');
        $nativeResult = $native->renameIndex(0, 'renamed.txt');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('original.txt', 'Content');
        $polyfillResult = $polyfill->renameIndex(0, 'renamed.txt');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // extractTo() Parity Tests
    // =========================================================================

    public function testExtractToParity(): void
    {
        $pid = getmypid();
        $tmp = sys_get_temp_dir();
        $nativeDir = "$tmp/continuum_zaparity_ext_native_$pid";
        $polyfillDir = "$tmp/continuum_zaparity_ext_polyfill_$pid";
        mkdir($nativeDir, 0755, true);
        mkdir($polyfillDir, 0755, true);

        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->extractTo($nativeDir);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->extractTo($polyfillDir);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
        Assert::same(
            file_get_contents("$nativeDir/hello.txt"),
            file_get_contents("$polyfillDir/hello.txt")
        );
        Assert::same(
            file_get_contents("$nativeDir/dir/nested.txt"),
            file_get_contents("$polyfillDir/dir/nested.txt")
        );

        $this->removeDir($nativeDir);
        $this->removeDir($polyfillDir);
    }

    public function testExtractToSpecificFilesParity(): void
    {
        $pid = getmypid();
        $tmp = sys_get_temp_dir();
        $nativeDir = "$tmp/continuum_zaparity_extsel_native_$pid";
        $polyfillDir = "$tmp/continuum_zaparity_extsel_polyfill_$pid";
        mkdir($nativeDir, 0755, true);
        mkdir($polyfillDir, 0755, true);

        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->extractTo($nativeDir, ['hello.txt']);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->extractTo($polyfillDir, ['hello.txt']);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
        Assert::same(
            file_get_contents("$nativeDir/hello.txt"),
            file_get_contents("$polyfillDir/hello.txt")
        );
        Assert::false(file_exists("$polyfillDir/dir/nested.txt"));

        $this->removeDir($nativeDir);
        $this->removeDir($polyfillDir);
    }

    // =========================================================================
    // getArchiveComment() / setArchiveComment() Parity Tests
    // =========================================================================

    public function testGetArchiveCommentEmptyParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getArchiveComment();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getArchiveComment();
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testSetAndGetArchiveCommentParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeSet = $native->setArchiveComment('Test comment');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillSet = $polyfill->setArchiveComment('Test comment');
        $polyfill->close();

        Assert::same($nativeSet, $polyfillSet);

        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);

        Assert::same($native->getArchiveComment(), $polyfill->getArchiveComment());
        Assert::same($native->comment, $polyfill->comment);

        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // getCommentName() / setCommentName() Parity Tests
    // =========================================================================

    public function testSetAndGetCommentNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeSet = $native->setCommentName('f.txt', 'entry comment');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillSet = $polyfill->setCommentName('f.txt', 'entry comment');
        $polyfill->close();

        Assert::same($nativeSet, $polyfillSet);

        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);

        Assert::same($native->getCommentName('f.txt'), $polyfill->getCommentName('f.txt'));

        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // getCommentIndex() / setCommentIndex() Parity Tests
    // =========================================================================

    public function testSetAndGetCommentIndexParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeSet = $native->setCommentIndex(0, 'entry comment');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillSet = $polyfill->setCommentIndex(0, 'entry comment');
        $polyfill->close();

        Assert::same($nativeSet, $polyfillSet);

        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);

        Assert::same($native->getCommentIndex(0), $polyfill->getCommentIndex(0));

        $native->close();
        $polyfill->close();
    }

    // =========================================================================
    // setCompressionName() / setCompressionIndex() Parity Tests
    // =========================================================================

    public function testSetCompressionNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeResult = $native->setCompressionName('f.txt', ZipArchive::CM_STORE);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillResult = $polyfill->setCompressionName('f.txt', ZipArchivePf::CM_STORE);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    public function testSetCompressionIndexParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeResult = $native->setCompressionIndex(0, ZipArchive::CM_DEFLATE);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillResult = $polyfill->setCompressionIndex(0, ZipArchivePf::CM_DEFLATE);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // setMtimeName() / setMtimeIndex() Parity Tests
    // =========================================================================

    public function testSetMtimeNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;
        $mtime = mktime(12, 0, 0, 6, 15, 2024);

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeResult = $native->setMtimeName('f.txt', $mtime);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillResult = $polyfill->setMtimeName('f.txt', $mtime);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);

        $native->open($this->nativePath);
        $polyfill->open($this->polyfillPath);

        Assert::same($native->statName('f.txt')['mtime'], $polyfill->statName('f.txt')['mtime']);

        $native->close();
        $polyfill->close();
    }

    public function testSetMtimeIndexParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;
        $mtime = mktime(12, 0, 0, 6, 15, 2024);

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeResult = $native->setMtimeIndex(0, $mtime);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillResult = $polyfill->setMtimeIndex(0, $mtime);
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // getExternalAttributesName() / setExternalAttributesName() Parity Tests
    // =========================================================================

    public function testExternalAttributesNameParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $nativeSet = $native->setExternalAttributesName('f.txt', ZipArchive::OPSYS_UNIX, 0644 << 16);
        $nativeOpsys = 0;
        $nativeAttr = 0;
        $nativeGet = $native->getExternalAttributesName('f.txt', $nativeOpsys, $nativeAttr);
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfillSet = $polyfill->setExternalAttributesName('f.txt', ZipArchivePf::OPSYS_UNIX, 0644 << 16);
        $polyfillOpsys = 0;
        $polyfillAttr = 0;
        $polyfillGet = $polyfill->getExternalAttributesName('f.txt', $polyfillOpsys, $polyfillAttr);
        $polyfill->close();

        Assert::same($nativeSet, $polyfillSet);
        Assert::same($nativeGet, $polyfillGet);
        Assert::same($nativeOpsys, $polyfillOpsys);
        Assert::same($nativeAttr, $polyfillAttr);
    }

    // =========================================================================
    // unchangeAll() / unchangeArchive() Parity Tests
    // =========================================================================

    public function testUnchangeAllParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $native->addFromString('new.txt', 'New');
        $nativeResult = $native->unchangeAll();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfill->addFromString('new.txt', 'New');
        $polyfillResult = $polyfill->unchangeAll();
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);

        // Verify the pending addition was discarded — compare count on a fresh open.
        $native->open($this->sourcePath);
        $polyfill->open($this->sourcePath);
        Assert::same($native->count(), $polyfill->count());
        Assert::false((bool)$native->locateName('new.txt'));
        Assert::false((bool)$polyfill->locateName('new.txt'));
        $native->close();
        $polyfill->close();
    }

    public function testUnchangeArchiveParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('f.txt', 'x');
        $native->setArchiveComment('temp');
        $nativeResult = $native->unchangeArchive();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('f.txt', 'x');
        $polyfill->setArchiveComment('temp');
        $polyfillResult = $polyfill->unchangeArchive();
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // setPassword() Parity Tests
    // =========================================================================

    public function testSetPasswordParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->setPassword('secret');
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->setPassword('secret');
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // getStatusString() Parity Tests
    // =========================================================================

    public function testGetStatusStringParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);
        $nativeResult = $native->getStatusString();
        $native->close();

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);
        $polyfillResult = $polyfill->getStatusString();
        $polyfill->close();

        Assert::same($nativeResult, $polyfillResult);
    }

    // =========================================================================
    // Public properties Parity Tests
    // =========================================================================

    public function testPublicPropertiesAfterOpenParity(): void
    {
        $native = new ZipArchive();
        $native->open($this->sourcePath);

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->sourcePath);

        Assert::same($native->status, $polyfill->status);
        Assert::same($native->numFiles, $polyfill->numFiles);
        Assert::same($native->comment, $polyfill->comment);
        Assert::same($native->lastId, $polyfill->lastId);

        $native->close();
        $polyfill->close();
    }

    public function testPublicPropertiesAfterAddParity(): void
    {
        $flags = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        $native = new ZipArchive();
        $native->open($this->nativePath, $flags);
        $native->addFromString('a.txt', 'A');
        $native->addFromString('b.txt', 'B');

        $polyfill = new ZipArchivePf();
        $polyfill->open($this->polyfillPath, $flags);
        $polyfill->addFromString('a.txt', 'A');
        $polyfill->addFromString('b.txt', 'B');

        Assert::same($native->status, $polyfill->status);
        Assert::same($native->numFiles, $polyfill->numFiles);
        Assert::same($native->lastId, $polyfill->lastId);

        $native->close();
        $polyfill->close();
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}

(new ZipArchiveParityTest())->run();
