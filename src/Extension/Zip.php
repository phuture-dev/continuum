<?php

declare(strict_types=1);

namespace Phuture\Continuum\Extension;

use stdClass;
use Throwable;
use PhpZip\ZipFile;
use PhpZip\Constants\ZipCompressionMethod;

/**
 * Zip polyfill methods.
 *
 * This class polyfills the legacy procedural ZIP functions (zip_open, zip_close,
 * zip_read, and the zip_entry_* family) that were removed in PHP 8.1. Each method
 * uses an internal handle registry backed by the nelexa/zip library to replicate
 * the original cursor-based API as closely as possible.
 *
 * Because PHP 8.0+ no longer supports the `resource` type for ZIP archives, archive
 * and entry handles are plain objects instead of resources. Code that checks
 * is_resource() must be updated to use is_object() instead.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Zip
{
    /**
     * Registry of open archive handles keyed by spl_object_id().
     *
     * Each record holds the ZipFile instance, an ordered list of entry names taken
     * from the central directory, and the cursor position used by zip_read() to
     * advance through the entries one at a time.
     *
     * @var array
     */
    private static array $archiveRegistry = [];

    /**
     * Registry of entry handles keyed by spl_object_id().
     *
     * Each record links back to its parent archive ID, holds the parsed ZipEntry
     * model for metadata access, and carries the open/read state needed by
     * zip_entry_open(), zip_entry_read(), and zip_entry_close().
     *
     * @var array
     */
    private static array $entryRegistry = [];

    /**
     * Opens a ZIP file archive for reading.
     *
     * It opens the given ZIP file and returns a handle object you can pass to zip_read()
     * to walk through entries one at a time, and to zip_close() when you are finished.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * if (is_object($archive)) {
     *     while ($entry = Zip::zip_read($archive)) {
     *         echo Zip::zip_entry_name($entry) . PHP_EOL;
     *     }
     *     Zip::zip_close($archive);
     * }
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-open.php
     * @see \Phuture\Continuum\Extension\Zip::zip_read()
     * @see \Phuture\Continuum\Extension\Zip::zip_close()
     *
     * @param string $filename The path to the ZIP file to open
     * @return mixed An archive handle object on success, or false if the file cannot be opened
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_open(string $filename): mixed
    {
        try {
            $zipFile = (new ZipFile())->openFile($filename);
        } catch (Throwable) {
            return false;
        }

        $handle   = new stdClass();
        $handleId = spl_object_id($handle);

        self::$archiveRegistry[$handleId] = [
            'zipFile' => $zipFile,
            'entryNames' => array_keys($zipFile->getEntries()),
            'cursor' => 0,
        ];

        return $handle;
    }

    /**
     * Closes an open ZIP file archive.
     *
     * This is a polyfill for the native zip_close() function that was removed in PHP 8.1.
     * It releases the archive handle and frees the underlying ZIP reader. Always call this
     * when you have finished reading from an archive opened with zip_open().
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * // ... read entries ...
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-close.php
     * @see \Phuture\Continuum\Extension\Zip::zip_open()
     *
     * @param mixed $zip The archive handle returned by zip_open()
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_close(mixed $zip): void
    {
        if (!($zip instanceof stdClass)) {
            return;
        }

        $handleId = spl_object_id($zip);

        if (!isset(self::$archiveRegistry[$handleId])) {
            return;
        }

        self::$archiveRegistry[$handleId]['zipFile']->close();
        unset(self::$archiveRegistry[$handleId]);
    }

    /**
     * Reads the next entry in an open ZIP archive.
     *
     * Each call moves an internal cursor forward and returns a handle for the next entry in
     * the archive. When there are no more entries, it returns false.
     *
     * To read the contents of the returned entry, pass the handle to zip_entry_open()
     * followed by zip_entry_read(). You can inspect an entry without opening it using
     * zip_entry_name(), zip_entry_filesize(), and zip_entry_compressedsize().
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * while ($entry = Zip::zip_read($archive)) {
     *     echo Zip::zip_entry_name($entry) . PHP_EOL;
     * }
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-read.php
     * @see \Phuture\Continuum\Extension\Zip::zip_open()
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_open()
     *
     * @param mixed $zip The archive handle returned by zip_open()
     * @return mixed An entry handle object on success, or false when there are no more entries
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_read(mixed $zip): mixed
    {
        if (!($zip instanceof stdClass)) {
            return false;
        }

        $archiveId = spl_object_id($zip);

        if (!isset(self::$archiveRegistry[$archiveId])) {
            return false;
        }

        $archive = &self::$archiveRegistry[$archiveId];

        if ($archive['cursor'] >= \count($archive['entryNames'])) {
            return false;
        }

        $entryName = $archive['entryNames'][$archive['cursor']++];
        $zipEntry  = $archive['zipFile']->getEntry($entryName);

        $entryHandle   = new stdClass();
        $entryHandleId = spl_object_id($entryHandle);

        self::$entryRegistry[$entryHandleId] = [
            'archiveId' => $archiveId,
            'zipEntry' => $zipEntry,
            'isOpen' => false,
            'content' => '',
            'readOffset' => 0,
        ];

        return $entryHandle;
    }

    /**
     * Opens a directory entry inside a ZIP archive for reading.
     *
     * Call this before using zip_entry_read() to access the contents of an entry. The entry's
     * full contents are loaded into memory when this method is called, so that zip_entry_read()
     * can return them in chunks. Directory entries are opened successfully and read as empty.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * if (Zip::zip_entry_open($archive, $entry)) {
     *     $data = Zip::zip_entry_read($entry);
     *     Zip::zip_entry_close($entry);
     * }
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-open.php
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_close()
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_read()
     *
     * @param mixed $zip The archive handle returned by zip_open()
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @param string $mode The read mode — only 'r' and 'rb' (read binary) are supported (default: 'rb')
     * @return bool Returns true on success, or false if the entry cannot be opened
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_open(mixed $zip, mixed $zip_entry, string $mode = 'rb'): bool
    {
        if (!($zip instanceof stdClass) || !($zip_entry instanceof stdClass)) {
            return false;
        }

        $archiveId = spl_object_id($zip);
        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$archiveRegistry[$archiveId], self::$entryRegistry[$entryHandleId])) {
            return false;
        }

        $zipEntry = self::$entryRegistry[$entryHandleId]['zipEntry'];

        try {
            $content = $zipEntry->isDirectory()
                ? ''
                : self::$archiveRegistry[$archiveId]['zipFile']->getEntryContents($zipEntry->getName());
        } catch (Throwable) {
            return false;
        }

        self::$entryRegistry[$entryHandleId]['isOpen'] = true;
        self::$entryRegistry[$entryHandleId]['content'] = $content;
        self::$entryRegistry[$entryHandleId]['readOffset'] = 0;

        return true;
    }

    /**
     * Closes an open directory entry.
     *
     * Call this after you are done reading from an entry to release its cached content from memory.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * Zip::zip_entry_open($archive, $entry);
     * // ... read from entry ...
     * Zip::zip_entry_close($entry);
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-close.php
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_open()
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return bool Returns true on success, or false if the entry handle is invalid
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_close(mixed $zip_entry): bool
    {
        if (!($zip_entry instanceof stdClass)) {
            return false;
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return false;
        }

        unset(self::$entryRegistry[$entryHandleId]);

        return true;
    }

    /**
     * Reads data from an open directory entry.
     *
     * It reads up to $length bytes from the entry, advancing an internal offset with each call
     * so you can read the entire entry in chunks. Returns an empty string when all data has been
     * read (end of file), and false if the entry has not been opened or an error occurs.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * $contents = '';
     * if (Zip::zip_entry_open($archive, $entry)) {
     *     while (($chunk = Zip::zip_entry_read($entry, 4096)) !== '') {
     *         $contents .= $chunk;
     *     }
     *     Zip::zip_entry_close($entry);
     * }
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-read.php
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_open()
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_close()
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @param int $length The maximum number of bytes to read (default: 1024)
     * @return string|false The data read as a string, an empty string at end of file, or false on failure
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_read(mixed $zip_entry, int $length = 1024): string|false
    {
        if (!($zip_entry instanceof stdClass)) {
            return false;
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return false;
        }

        $entryState = &self::$entryRegistry[$entryHandleId];

        if (!$entryState['isOpen']) {
            return false;
        }

        $chunk = substr($entryState['content'], $entryState['readOffset'], $length);
        $entryState['readOffset'] += strlen($chunk);

        return $chunk;
    }

    /**
     * Retrieves the compressed size of a directory entry.
     *
     * The compressed size is the number of bytes the entry occupies inside the ZIP
     * archive — that is, after compression has been applied. It equals zip_entry_filesize() when
     * the entry uses the 'stored' method (no compression).
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * echo Zip::zip_entry_compressedsize($entry); // e.g. 1024
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-compressedsize.php
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_filesize()
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return int The size of the entry in the archive in bytes, or 0 if the handle is invalid
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_compressedsize(mixed $zip_entry): int
    {
        if (!($zip_entry instanceof stdClass)) {
            return 0;
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return 0;
        }

        return self::$entryRegistry[$entryHandleId]['zipEntry']->getCompressedSize();
    }

    /**
     * Retrieves the compression method of a directory entry.
     *
     * It returns the lowercase name of the algorithm used to compress the entry,
     * such as 'stored' (no compression), 'deflated' (standard ZIP deflate), or 'bzip2'.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * echo Zip::zip_entry_compressionmethod($entry); // e.g. 'deflated'
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-compressionmethod.php
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return string The lowercase name of the compression method, or an empty string if the handle is invalid
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_compressionmethod(mixed $zip_entry): string
    {
        if (!($zip_entry instanceof stdClass)) {
            return '';
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return '';
        }

        $compressionMethodId = self::$entryRegistry[$entryHandleId]['zipEntry']->getCompressionMethod();

        return strtolower(ZipCompressionMethod::getCompressionMethodName($compressionMethodId));
    }

    /**
     * Retrieves the uncompressed file size of a directory entry.
     *
     * The file size is the number of bytes the entry would occupy after extraction — that is, before
     * any compression. This equals zip_entry_compressedsize() when the entry uses the 'stored' method.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * $entry = Zip::zip_read($archive);
     * echo Zip::zip_entry_filesize($entry); // e.g. 4096
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-filesize.php
     * @see \Phuture\Continuum\Extension\Zip::zip_entry_compressedsize()
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return int The uncompressed size of the entry in bytes, or 0 if the handle is invalid
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_filesize(mixed $zip_entry): int
    {
        if (!($zip_entry instanceof stdClass)) {
            return 0;
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return 0;
        }

        return self::$entryRegistry[$entryHandleId]['zipEntry']->getUncompressedSize();
    }

    /**
     * Retrieves the name of a directory entry.
     *
     * It returns the path of the entry within the archive — for example 'images/photo.jpg' or
     * 'README.txt'. Directory entries end with a forward slash, for example 'images/'.
     *
     * Example:
     * ```php
     * use Phuture\Continuum\Extension\Zip;
     *
     * $archive = Zip::zip_open('/path/to/archive.zip');
     * while ($entry = Zip::zip_read($archive)) {
     *     echo Zip::zip_entry_name($entry) . PHP_EOL;
     * }
     * Zip::zip_close($archive);
     * ```
     *
     * @see https://www.php.net/manual/en/function.zip-entry-name.php
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return string The path of the entry inside the archive, or an empty string if the handle is invalid
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function zip_entry_name(mixed $zip_entry): string
    {
        if (!($zip_entry instanceof stdClass)) {
            return '';
        }

        $entryHandleId = spl_object_id($zip_entry);

        if (!isset(self::$entryRegistry[$entryHandleId])) {
            return '';
        }

        return self::$entryRegistry[$entryHandleId]['zipEntry']->getName();
    }
}
