<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Extension\Zip;

if (extension_loaded('zip') && \PHP_VERSION_ID <= 80100) {
    return;
}

/**
 * Zip functions
 */
if (!function_exists('zip_open')) {
    /**
     * Opens a ZIP file archive for reading.
     *
     * @param string $filename The path to the ZIP file to open
     * @return mixed An archive handle object on success, or false if the file cannot be opened
     */
    function zip_open(string $filename): mixed
    {
        return Zip::zip_open($filename);
    }
}

if (!function_exists('zip_close')) {
    /**
     * Closes an open ZIP file archive.
     *
     * @param mixed $zip The archive handle returned by zip_open()
     */
    function zip_close(mixed $zip): void
    {
        Zip::zip_close($zip);
    }
}

if (!function_exists('zip_read')) {
    /**
     * Reads the next entry in an open ZIP archive.
     *
     * @param mixed $zip The archive handle returned by zip_open()
     * @return mixed An entry handle object on success, or false when there are no more entries
     */
    function zip_read(mixed $zip): mixed
    {
        return Zip::zip_read($zip);
    }
}

if (!function_exists('zip_entry_open')) {
    /**
     * Opens a directory entry inside a ZIP archive for reading.
     *
     * @param mixed $zip The archive handle returned by zip_open()
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @param string $mode The read mode (default: 'rb')
     * @return bool Returns true on success, or false if the entry cannot be opened
     */
    function zip_entry_open(mixed $zip, mixed $zip_entry, string $mode = 'rb'): bool
    {
        return Zip::zip_entry_open($zip, $zip_entry, $mode);
    }
}

if (!function_exists('zip_entry_close')) {
    /**
     * Closes an open directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return bool Returns true on success, or false if the entry handle is invalid
     */
    function zip_entry_close(mixed $zip_entry): bool
    {
        return Zip::zip_entry_close($zip_entry);
    }
}

if (!function_exists('zip_entry_read')) {
    /**
     * Reads data from an open directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @param int $length The maximum number of bytes to read (default: 1024)
     * @return string|false The data read, an empty string at end of file, or false on failure
     */
    function zip_entry_read(mixed $zip_entry, int $length = 1024): string|false
    {
        return Zip::zip_entry_read($zip_entry, $length);
    }
}

if (!function_exists('zip_entry_compressedsize')) {
    /**
     * Retrieves the compressed size of a directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return int The size of the entry in the archive in bytes, or 0 if the handle is invalid
     */
    function zip_entry_compressedsize(mixed $zip_entry): int
    {
        return Zip::zip_entry_compressedsize($zip_entry);
    }
}

if (!function_exists('zip_entry_compressionmethod')) {
    /**
     * Retrieves the compression method of a directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return string The lowercase name of the compression method, or an empty string if the handle is invalid
     */
    function zip_entry_compressionmethod(mixed $zip_entry): string
    {
        return Zip::zip_entry_compressionmethod($zip_entry);
    }
}

if (!function_exists('zip_entry_filesize')) {
    /**
     * Retrieves the uncompressed file size of a directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return int The uncompressed size of the entry in bytes, or 0 if the handle is invalid
     */
    function zip_entry_filesize(mixed $zip_entry): int
    {
        return Zip::zip_entry_filesize($zip_entry);
    }
}

if (!function_exists('zip_entry_name')) {
    /**
     * Retrieves the name of a directory entry.
     *
     * @param mixed $zip_entry The entry handle returned by zip_read()
     * @return string The path of the entry inside the archive, or an empty string if the handle is invalid
     */
    function zip_entry_name(mixed $zip_entry): string
    {
        return Zip::zip_entry_name($zip_entry);
    }
}
