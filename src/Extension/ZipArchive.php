<?php

declare(strict_types=1);

namespace Phuture\Continuum\Extension;

use Countable;
use Throwable;
use PhpZip\ZipFile;
use FilesystemIterator;
use PhpZip\Model\ZipEntry;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpZip\Constants\{ZipCompressionMethod, ZipEncryptionMethod};

/**
 * ZipArchive polyfill class.
 *
 * A drop-in replacement for PHP's built-in ZipArchive class (from ext-zip) for environments
 * where the zip extension is not available. All methods delegate to the nelexa/zip library
 * (PhpZip\ZipFile) under the hood. The public API — constants, properties, and method
 * signatures, exactly mirrors the native ZipArchive class.
 *
 * A small number of methods that depend on libzip internals cannot be implemented through
 * nelexa/zip and return false or 0, as documented per method.
 *
 * @see https://www.php.net/manual/en/class.ziparchive.php
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
// phpcs:ignore
class ZipArchive implements Countable
{
    /**
     * Open flag: create a new archive if the file does not already exist.
     * Combine with OVERWRITE to always start fresh. Without CREATE, open() returns
     * ER_NOENT when the path does not exist.
     *
     * @var int
     */
    public const CREATE = 1;

    /**
     * Open flag: fail if the archive file already exists.
     * When used with open() and the path already exists, open() returns ER_EXISTS.
     * Use CREATE instead if you want to create the file only when it is absent.
     *
     * @var int
     */
    public const EXCL = 2;

    /**
     * Open flag: perform additional consistency checks on the archive.
     * Causes open() to verify internal ZIP structures more strictly and return ER_INCONS
     * if any inconsistencies are detected.
     *
     * @var int
     */
    public const CHECKCONS = 4;

    /**
     * Open flag: always start with an empty archive, discarding any existing file.
     * Any file already at the given path is overwritten on close(). Use this to reset
     * an archive to an empty state without deleting and re-creating the file manually.
     *
     * @var int
     */
    public const OVERWRITE = 8;

    /**
     * Open flag: open the archive in read-only mode.
     * Prevents any modifications from being written back to disk on close(). Methods that
     * would modify the archive (addFile, deleteName, etc.) return false when this flag is set.
     *
     * @var int
     */
    public const RDONLY = 16;

    /**
     * Open flag: read local file headers instead of the central directory.
     * Normally the ZIP central directory is used to list entries. This flag forces reading
     * local file headers directly, which is useful for partially-written archives.
     *
     * @var int
     */
    public const FL_OPEN_FILE_NOW = 2048;

    /**
     * Entry-lookup flag: ignore case when matching entry names.
     * Pass this in the $flags parameter of getFromName(), locateName(), statName(), and
     * similar methods to perform a case-insensitive name lookup.
     *
     * @var int
     */
    public const FL_NOCASE = 1;

    /**
     * Entry-lookup flag: ignore directory components when matching entry names.
     * When set, only the last path component of an entry name is compared against the
     * search term.
     *
     * @var int
     */
    public const FL_NODIR = 2;

    /**
     * Entry-lookup flag: return compressed data instead of decompressing.
     * When set, methods such as getFromName() return the raw compressed bytes rather than
     * the decompressed file content.
     *
     * @var int
     */
    public const FL_COMPRESSED = 4;

    /**
     * Entry-lookup flag: use the original, unmodified data.
     * When set, read operations return the entry content as it was when the archive was
     * opened, ignoring any modifications made in the current session.
     *
     * @var int
     */
    public const FL_UNCHANGED = 8;

    /**
     * Entry-lookup flag: force recompression of data.
     * Instructs the library to recompress the entry data even if it is already compressed.
     *
     * @var int
     */
    public const FL_RECOMPRESS = 16;

    /**
     * Entry-lookup flag: read encrypted data without decrypting.
     * Implies FL_COMPRESSED. Returns the raw encrypted, compressed bytes of the entry.
     *
     * @var int
     */
    public const FL_ENCRYPTED = 32;

    /**
     * Entry-add flag: overwrite an existing entry that has the same name.
     * Pass this in the $flags parameter of addFile() or addFromString() to replace an
     * entry that already exists rather than creating a duplicate.
     *
     * @var int
     */
    public const FL_OVERWRITE = 8192;

    /**
     * Entry-lookup flag: operate on data in the local file header.
     * Directs the operation to use metadata from the local file header rather than the
     * central directory.
     *
     * @var int
     */
    public const FL_LOCAL = 256;

    /**
     * Entry-lookup flag: operate on data in the central directory.
     * Directs the operation to use metadata from the central directory record rather than
     * the local file header.
     *
     * @var int
     */
    public const FL_CENTRAL = 512;

    /**
     * String-encoding flag: guess the encoding automatically (default behaviour).
     * The library attempts to detect whether entry names are stored as UTF-8 or CP437 and
     * decodes them accordingly.
     *
     * @var int
     */
    public const FL_ENC_GUESS = 0;

    /**
     * String-encoding flag: return the raw, unmodified byte string.
     * No encoding conversion is applied; the bytes are returned exactly as stored in the
     * archive.
     *
     * @var int
     */
    public const FL_ENC_RAW = 64;

    /**
     * String-encoding flag: follow the ZIP specification strictly.
     * Treats strings as ASCII and rejects anything outside the ASCII range without
     * attempting to fall back to an alternative encoding.
     *
     * @var int
     */
    public const FL_ENC_STRICT = 128;

    /**
     * String-encoding flag: treat entry name strings as UTF-8 encoded.
     * Forces interpretation of entry name bytes as UTF-8, overriding any auto-detection.
     *
     * @var int
     */
    public const FL_ENC_UTF_8 = 2048;

    /**
     * String-encoding flag: treat entry name strings as CP437 encoded.
     * Forces interpretation of entry name bytes as IBM Code Page 437, the original DOS
     * encoding used by most legacy ZIP files.
     *
     * @var int
     */
    public const FL_ENC_CP437 = 4096;

    /**
     * Compression method sentinel: use the library default (deflate).
     * Pass this to setCompressionName() or setCompressionIndex() to let the library
     * pick CM_DEFLATE automatically.
     *
     * @var int
     */
    public const CM_DEFAULT = -1;

    /**
     * Compression method: store without any compression (method 0).
     * The file bytes are written as-is into the archive. Useful for already-compressed
     * files (images, videos) where deflating provides no benefit.
     *
     * @var int
     */
    public const CM_STORE = 0;

    /**
     * Compression method: Shrink — an obsolete LZW-based algorithm (method 1).
     * Defined for compatibility with very old archives. Not supported for writing in
     * modern implementations.
     *
     * @var int
     */
    public const CM_SHRINK = 1;

    /**
     * Compression method: Reduce with compression factor 1 — obsolete (method 2).
     * A series of four historical Reduce methods (factors 1–4). Kept for reading legacy
     * archives only; not produced by modern tools.
     *
     * @var int
     */
    public const CM_REDUCE_1 = 2;

    /**
     * Compression method: Reduce with compression factor 2 — obsolete (method 3).
     *
     * @var int
     */
    public const CM_REDUCE_2 = 3;

    /**
     * Compression method: Reduce with compression factor 3 — obsolete (method 4).
     *
     * @var int
     */
    public const CM_REDUCE_3 = 4;

    /**
     * Compression method: Reduce with compression factor 4 — obsolete (method 5).
     *
     * @var int
     */
    public const CM_REDUCE_4 = 5;

    /**
     * Compression method: Implode — an obsolete compression algorithm (method 6).
     * A historical PKWARE algorithm that pre-dates deflate. Modern tools only read it
     * for backwards compatibility.
     *
     * @var int
     */
    public const CM_IMPLODE = 6;

    /**
     * Compression method: Deflate — the standard ZIP compression algorithm (method 8).
     * The most universally supported method. Use CM_DEFAULT to let the library choose
     * between STORE and DEFLATE automatically.
     *
     * @var int
     */
    public const CM_DEFLATE = 8;

    /**
     * Compression method: Deflate64 — an enhanced version of Deflate (method 9).
     * Allows larger windows than standard Deflate but has limited tool support. Only
     * defined here for reading legacy archives.
     *
     * @var int
     */
    public const CM_DEFLATE64 = 9;

    /**
     * Compression method: PKWARE Data Compression Library Imploding — obsolete (method 10).
     * A PKWARE-proprietary variant of implode. Kept for historical compatibility only.
     *
     * @var int
     */
    public const CM_PKWARE_IMPLODE = 10;

    /**
     * Compression method: BZIP2 (method 12).
     * Supported for reading and writing when the PHP bz2 extension is available. Check
     * availability at runtime with ZipArchive::isCompressionMethodSupported(CM_BZIP2).
     *
     * @var int
     */
    public const CM_BZIP2 = 12;

    /**
     * Compression method: LZMA (method 14).
     * Not supported for writing in this polyfill. Defined for reading archives produced
     * by tools that use LZMA compression.
     *
     * @var int
     */
    public const CM_LZMA = 14;

    /**
     * Compression method: LZMA2 (method 33).
     * The successor to LZMA used in XZ archives. Defined for completeness; not available
     * for writing in this polyfill.
     *
     * @var int
     */
    public const CM_LZMA2 = 33;

    /**
     * Compression method: Zstandard (method 93).
     * A modern high-performance algorithm. Defined for completeness; support depends on
     * the underlying library version.
     *
     * @var int
     */
    public const CM_ZSTD = 93;

    /**
     * Compression method: XZ (method 95).
     * Uses the LZMA2 algorithm in an XZ container. Defined for completeness; not
     * available for writing in this polyfill.
     *
     * @var int
     */
    public const CM_XZ = 95;

    /**
     * Compression method: IBM TERSE (method 18).
     * An IBM mainframe compression algorithm. Defined for reading legacy mainframe
     * archives only.
     *
     * @var int
     */
    public const CM_TERSE = 18;

    /**
     * Compression method: IBM LZ77 z Architecture (method 19).
     * An IBM-specific LZ77 variant. Defined for reading legacy IBM archives only.
     *
     * @var int
     */
    public const CM_LZ77 = 19;

    /**
     * Compression method: WavPack (method 97).
     * A lossless audio compression format. Defined for completeness; rarely seen in
     * general-purpose ZIP archives.
     *
     * @var int
     */
    public const CM_WAVPACK = 97;

    /**
     * Compression method: PPMd (method 98).
     * A prediction-by-partial-matching compression algorithm. Defined for completeness;
     * support depends on the underlying library version.
     *
     * @var int
     */
    public const CM_PPMD = 98;

    /**
     * Error code: no error occurred.
     * The operation completed successfully. open() returns this via the $status property
     * and getStatusString() returns 'No error'.
     *
     * @var int
     */
    public const ER_OK = 0;

    /**
     * Error code: multi-disk ZIP archives are not supported.
     * The archive spans multiple disk volumes, which this implementation cannot handle.
     *
     * @var int
     */
    public const ER_MULTIDISK = 1;

    /**
     * Error code: renaming the temporary output file failed.
     * After writing changes, the library moves a temporary file into place. This error
     * means that move failed.
     *
     * @var int
     */
    public const ER_RENAME = 2;

    /**
     * Error code: closing the ZIP archive failed.
     * An I/O error occurred while finalising or flushing the archive to disk.
     *
     * @var int
     */
    public const ER_CLOSE = 3;

    /**
     * Error code: a seek operation on the archive file failed.
     * The underlying file descriptor could not be repositioned, typically because the
     * file was deleted or the storage device became unavailable.
     *
     * @var int
     */
    public const ER_SEEK = 4;

    /**
     * Error code: a read operation on the archive file failed.
     * An I/O error occurred while reading data from the archive.
     *
     * @var int
     */
    public const ER_READ = 5;

    /**
     * Error code: a write operation on the archive file failed.
     * An I/O error occurred while writing data to the archive, for example because the
     * disk is full.
     *
     * @var int
     */
    public const ER_WRITE = 6;

    /**
     * Error code: a CRC-32 checksum mismatch was detected.
     * The stored CRC of an entry does not match the CRC computed from the actual data,
     * indicating archive corruption.
     *
     * @var int
     */
    public const ER_CRC = 7;

    /**
     * Error code: the containing ZIP archive was closed while a stream was still open.
     * A stream obtained from getStream() or similar was in use when close() was called.
     *
     * @var int
     */
    public const ER_ZIPCLOSED = 8;

    /**
     * Error code: the requested entry or file does not exist.
     * Returned by open() when the path is absent and no CREATE/EXCL/OVERWRITE flag was
     * supplied, or by entry-lookup methods when the name is not found.
     *
     * @var int
     */
    public const ER_NOENT = 9;

    /**
     * Error code: the file already exists.
     * Returned by open() when the EXCL flag is set and the path already exists.
     *
     * @var int
     */
    public const ER_EXISTS = 10;

    /**
     * Error code: the archive file could not be opened.
     * The path exists but the file could not be opened for reading or writing, typically
     * due to a permissions problem.
     *
     * @var int
     */
    public const ER_OPEN = 11;

    /**
     * Error code: a temporary file could not be created.
     * The library writes changes to a temporary file before renaming it into place. This
     * error means the temporary file could not be created.
     *
     * @var int
     */
    public const ER_TMPOPEN = 12;

    /**
     * Error code: a zlib decompression or compression error occurred.
     * The zlib library reported an error while inflating or deflating entry data.
     *
     * @var int
     */
    public const ER_ZLIB = 13;

    /**
     * Error code: a memory allocation failed.
     * The library could not allocate enough memory to complete the operation.
     *
     * @var int
     */
    public const ER_MEMORY = 14;

    /**
     * Error code: the entry has been changed and cannot be read in its original form.
     * Returned when FL_UNCHANGED is set but the entry has pending modifications.
     *
     * @var int
     */
    public const ER_CHANGED = 15;

    /**
     * Error code: the requested compression method is not supported.
     * The archive uses a compression algorithm that this implementation cannot handle.
     *
     * @var int
     */
    public const ER_COMPNOTSUPP = 16;

    /**
     * Error code: unexpected end of file reached.
     * The archive data ended before the expected amount of data was read, indicating a
     * truncated or corrupt archive.
     *
     * @var int
     */
    public const ER_EOF = 17;

    /**
     * Error code: an invalid argument was supplied.
     * A parameter value is outside the valid range or is otherwise not accepted.
     *
     * @var int
     */
    public const ER_INVAL = 18;

    /**
     * Error code: the file is not a ZIP archive.
     * Returned by open() when the file at the given path does not have a valid ZIP
     * end-of-central-directory signature.
     *
     * @var int
     */
    public const ER_NOZIP = 19;

    /**
     * Error code: an internal library error occurred.
     * An unexpected condition was detected inside the ZIP library. This is typically a
     * bug in the library or a severely corrupt archive.
     *
     * @var int
     */
    public const ER_INTERNAL = 20;

    /**
     * Error code: the ZIP archive is internally inconsistent.
     * The central directory and local file headers disagree in a way that the library
     * cannot reconcile. The archive may be partially corrupt.
     *
     * @var int
     */
    public const ER_INCONS = 21;

    /**
     * Error code: the archive file could not be removed.
     * An attempt to delete a temporary or intermediate file during processing failed.
     *
     * @var int
     */
    public const ER_REMOVE = 22;

    /**
     * Error code: the entry has been marked for deletion.
     * An operation was attempted on an entry that has already been deleted in the current
     * session.
     *
     * @var int
     */
    public const ER_DELETED = 23;

    /**
     * Error code: the requested encryption method is not supported.
     * The archive uses an encryption algorithm that this implementation cannot handle.
     *
     * @var int
     */
    public const ER_ENCRNOTSUPP = 24;

    /**
     * Error code: the archive is read-only and cannot be modified.
     * Returned when a write operation is attempted on an archive opened with RDONLY.
     *
     * @var int
     */
    public const ER_RDONLY = 25;

    /**
     * Error code: no password was provided to decrypt an encrypted entry.
     * An encrypted entry was accessed without first calling setPassword().
     *
     * @var int
     */
    public const ER_NOPASSWD = 26;

    /**
     * Error code: the password provided does not decrypt the entry correctly.
     * The supplied password was accepted syntactically but the decrypted data fails its
     * integrity check.
     *
     * @var int
     */
    public const ER_WRONGPASSWD = 27;

    /**
     * Error code: the requested operation is not supported.
     * The operation cannot be performed on this type of archive or entry.
     *
     * @var int
     */
    public const ER_OPNOTSUPP = 28;

    /**
     * Error code: the resource is still in use.
     * An attempt was made to close or modify a resource while it is still referenced
     * by an open stream or another active operation.
     *
     * @var int
     */
    public const ER_INUSE = 29;

    /**
     * Error code: a tell operation on the archive file failed.
     * The library could not determine the current position in the file stream.
     *
     * @var int
     */
    public const ER_TELL = 30;

    /**
     * Error code: the compressed data of an entry is invalid.
     * Decompression of the entry data produced an error or unexpected result, suggesting
     * the compressed bytes are corrupt.
     *
     * @var int
     */
    public const ER_COMPRESSED_DATA = 31;

    /**
     * Error code: the operation was cancelled by a cancel callback.
     * A callback registered with registerCancelCallback() returned a non-zero value,
     * causing the current operation to stop.
     *
     * @var int
     */
    public const ER_CANCELLED = 32;

    /**
     * Error code: the data length does not match the expected value.
     * The amount of data provided or read does not match the length recorded in the
     * archive headers.
     *
     * @var int
     */
    public const ER_DATA_LENGTH = 33;

    /**
     * Error code: the operation is not allowed in a torrentzip archive.
     * Torrentzip archives have a fixed layout; certain modifications are prohibited to
     * preserve the canonical form.
     *
     * @var int
     */
    public const ER_NOT_ALLOWED = 34;

    /**
     * Error code: the ZIP file is truncated.
     * The archive ends earlier than the central directory or local headers indicate,
     * suggesting the file was not completely written.
     *
     * @var int
     */
    public const ER_TRUNCATED_ZIP = 35;

    /**
     * Operating system: MS-DOS and OS/2 (value 0).
     * Used in external attributes to indicate that file permissions follow the MS-DOS
     * attribute model.
     *
     * @var int
     */
    public const OPSYS_DOS = 0;

    /**
     * Operating system: Amiga (value 1).
     *
     * @var int
     */
    public const OPSYS_AMIGA = 1;

    /**
     * Operating system: OpenVMS (value 2).
     *
     * @var int
     */
    public const OPSYS_OPENVMS = 2;

    /**
     * Operating system: UNIX (value 3).
     * Used in external attributes to store POSIX file permissions. Permissions are
     * encoded in the high 16 bits of the attribute integer (mode << 16).
     *
     * @var int
     */
    public const OPSYS_UNIX = 3;

    /**
     * Operating system: IBM VM/CMS (value 4).
     *
     * @var int
     */
    public const OPSYS_VM_CMS = 4;

    /**
     * Operating system: Atari ST (value 5).
     *
     * @var int
     */
    public const OPSYS_ATARI_ST = 5;

    /**
     * Operating system: OS/2 HPFS (value 6).
     *
     * @var int
     */
    public const OPSYS_OS_2 = 6;

    /**
     * Operating system: classic Macintosh (value 7).
     *
     * @var int
     */
    public const OPSYS_MACINTOSH = 7;

    /**
     * Operating system: Z-System (value 8).
     *
     * @var int
     */
    public const OPSYS_Z_SYSTEM = 8;

    /**
     * Operating system: CP/M (value 9).
     *
     * @var int
     */
    public const OPSYS_CPM = 9;

    /**
     * Operating system: Windows NTFS (value 10).
     * Used in external attributes to store NTFS timestamps and file attributes.
     *
     * @var int
     */
    public const OPSYS_WINDOWS_NTFS = 10;

    /**
     * Operating system: IBM MVS (value 11).
     *
     * @var int
     */
    public const OPSYS_MVS = 11;

    /**
     * Operating system: IBM VSE (value 12).
     *
     * @var int
     */
    public const OPSYS_VSE = 12;

    /**
     * Operating system: Acorn RISC OS (value 13).
     *
     * @var int
     */
    public const OPSYS_ACORN_RISC = 13;

    /**
     * Operating system: VFAT (value 14).
     * Used for FAT32 and exFAT file systems, common on USB drives and Windows.
     *
     * @var int
     */
    public const OPSYS_VFAT = 14;

    /**
     * Operating system: alternate MVS (value 15).
     *
     * @var int
     */
    public const OPSYS_ALTERNATE_MVS = 15;

    /**
     * Operating system: BeOS (value 16).
     *
     * @var int
     */
    public const OPSYS_BEOS = 16;

    /**
     * Operating system: Tandem (value 17).
     *
     * @var int
     */
    public const OPSYS_TANDEM = 17;

    /**
     * Operating system: IBM OS/400 (value 18).
     *
     * @var int
     */
    public const OPSYS_OS_400 = 18;

    /**
     * Operating system: OS X / macOS Darwin (value 19).
     * Used in external attributes to store POSIX permissions for files created on macOS.
     *
     * @var int
     */
    public const OPSYS_OS_X = 19;

    /**
     * Default operating system: UNIX.
     * Alias for OPSYS_UNIX. Use this constant when you want to indicate UNIX permissions
     * without hard-coding the numeric value 3.
     *
     * @var int
     */
    public const OPSYS_DEFAULT = self::OPSYS_UNIX;

    /**
     * Encryption method: no encryption (plaintext).
     * Entries with this method are stored without any password protection. Use this
     * constant with setEncryptionName() to remove encryption from an entry.
     *
     * @var int
     */
    public const EM_NONE = 0;

    /**
     * Encryption method: traditional PKWARE encryption (ZipCrypto).
     * The original PKWARE encryption scheme. It is weak by modern standards and should
     * only be used when compatibility with old software is required.
     *
     * @var int
     */
    public const EM_TRAD_PKWARE = 1;

    /**
     * Encryption method: WinZip AES-128 encryption.
     * Uses 128-bit AES in CTR mode as defined in the WinZip AES specification. Stronger
     * than PKWARE encryption. Check availability with isEncryptionMethodSupported().
     *
     * @var int
     */
    public const EM_AES_128 = 257;

    /**
     * Encryption method: WinZip AES-192 encryption.
     * Uses 192-bit AES in CTR mode. A middle ground between AES-128 and AES-256 in
     * terms of key strength.
     *
     * @var int
     */
    public const EM_AES_192 = 258;

    /**
     * Encryption method: WinZip AES-256 encryption.
     * Uses 256-bit AES in CTR mode. The strongest encryption method supported by this
     * polyfill. Recommended for new archives that require encryption.
     *
     * @var int
     */
    public const EM_AES_256 = 259;

    /**
     * Encryption method sentinel: the method is unknown or unrecognised.
     * Returned by statName() / statIndex() when an entry uses an encryption algorithm
     * that the library does not recognise.
     *
     * @var int
     */
    public const EM_UNKNOWN = 65535;

    /**
     * Archive flag: the archive is read-only.
     * When this flag is set on the archive, no modifications can be saved. Corresponds
     * to the libzip AFL_RDONLY flag.
     *
     * @var int
     */
    public const AFL_RDONLY = 2;

    /**
     * Archive flag: the archive is a torrentzip archive.
     * Indicates that the archive conforms to the torrentzip canonical format, which fixes
     * compression settings and entry ordering for deterministic output.
     *
     * @var int
     */
    public const AFL_IS_TORRENTZIP = 4;

    /**
     * Archive flag: write the archive in torrentzip format.
     * When set, the library writes the archive in the canonical torrentzip format,
     * ensuring a deterministic byte-for-byte output.
     *
     * @var int
     */
    public const AFL_WANT_TORRENTZIP = 8;

    /**
     * Archive flag: create or keep the archive file even when it contains no entries.
     * Normally an empty archive is not written to disk. This flag overrides that
     * behaviour and ensures the file is always created or preserved.
     *
     * @var int
     */
    public const AFL_CREATE_OR_KEEP_FILE_FOR_EMPTY_ARCHIVE = 16;

    /**
     * Length sentinel: read all data from the current position to end of file.
     * Pass this as the $length argument to addFile() or getFromName() to read the entire
     * remaining content of the file or entry.
     *
     * @var int
     */
    public const LENGTH_TO_END = 0;

    /**
     * Length sentinel: read data without verifying the length.
     * Instructs the library to return whatever data is available without comparing the
     * amount against the length stored in the archive headers.
     *
     * @var int
     */
    public const LENGTH_UNCHECKED = -1;

    /**
     * The version of the libzip library that this polyfill emulates.
     * Matches the libzip version string that the native ext-zip extension would report
     * at the time this polyfill was written.
     *
     * @var string
     */
    public const LIBZIP_VERSION = '1.11.2';

    /**
     * Status code of the last operation.
     * Holds one of the ER_* error code constants. Reset to ER_OK (0) at the start of
     * each successful operation. Inspect this after a method returns false to understand
     * what went wrong. Use getStatusString() for a human-readable message.
     *
     * @var int
     */
    public int $status = 0;

    /**
     * OS-level status code of the last operation.
     * In the native ext-zip extension this holds the errno value from the operating
     * system when an I/O error occurs. This polyfill always leaves it at 0.
     *
     * @var int
     */
    public int $statusSys = 0;

    /**
     * Number of entries in the currently open archive.
     * Updated automatically after every operation that adds, deletes, or undeletes an
     * entry. Mirrors the return value of count() and equals the value of PHP's count()
     * function when called on the ZipArchive instance.
     *
     * @var int
     */
    public int $numFiles = 0;

    /**
     * Absolute path of the currently open archive file.
     * Set by open() using realpath() so symlinks are always resolved. Reset to an empty
     * string by close() after the archive is saved and closed.
     *
     * @var string
     */
    public string $filename = '';

    /**
     * Global comment of the currently open archive.
     * Kept in sync with the underlying ZipFile state by updatePublicProperties(). Set
     * directly via setArchiveComment() or reverted with unchangeArchive().
     *
     * @var string
     */
    public string $comment = '';

    /**
     * Zero-based index of the last entry added to the archive.
     * Updated by addFile() and addFromString() after each successful add operation.
     * Starts at -1 (no entries added yet) and resets to -1 when the archive is closed.
     *
     * @var int
     */
    public int $lastId = -1;

    /**
     * The underlying nelexa/zip archive instance.
     * Null when no archive is open. Set by open() and cleared by close(). All
     * public methods that operate on entries guard against a null value here.
     *
     * @var ZipFile|null
     */
    private ?ZipFile $zipFile = null;

    /**
     * Whether the archive was opened in read-only mode.
     * Set to true when RDONLY is passed to open(). Methods that would write to the
     * archive check this flag and return false immediately when it is true.
     *
     * @var bool
     */
    private bool $readOnly = false;

    /**
     * Archive-wide password used for encrypting and decrypting entries.
     * Stored by setPassword() and used as a fallback in setEncryptionName() when no
     * per-entry password is supplied. Reset to an empty string by close().
     *
     * @var string
     */
    private string $globalPassword = '';

    /**
     * Opens a ZIP archive for reading, writing, or creating.
     * This method opens the archive at the given path. How it behaves depends on the $flags
     * you provide. With no flags, the file must already exist and be a valid ZIP. With CREATE,
     * it will be created if it does not exist. With OVERWRITE, any existing file is discarded
     * and a fresh empty archive is started. With EXCL, the call fails if the file already
     * exists. With RDONLY, the archive is opened for reading only.
     * When this method returns true, you can use the other methods to add, read, and modify
     * entries. Call close() when you are finished to save the archive back to disk.
     *
     * @param string $filename Path to the ZIP archive to open or create
     * @param int $flags Combination of ZipArchive::CREATE, EXCL, CHECKCONS, OVERWRITE, RDONLY (default: 0)
     * @return bool|int Returns true on success, or one of the ER_* error code integers on failure
     * @see \ZipArchive::close()
     */
    public function open(string $filename, int $flags = 0): bool|int
    {
        $fileExists = file_exists($filename);
        $excl = (bool) ($flags & self::EXCL);
        $overwrite = (bool) ($flags & self::OVERWRITE);
        $create = (bool) ($flags & self::CREATE);
        $rdOnly = (bool) ($flags & self::RDONLY);

        if ($excl && $fileExists) {
            $this->status = self::ER_EXISTS;

            return self::ER_EXISTS;
        }

        if (!$fileExists && !$create && !$excl && !$overwrite) {
            $this->status = self::ER_NOENT;

            return self::ER_NOENT;
        }

        try {
            $zipFile = new ZipFile();

            if ($fileExists && !$overwrite) {
                $zipFile->openFile($filename);
            }
        } catch (Throwable) {
            $this->status = self::ER_NOZIP;

            return self::ER_NOZIP;
        }

        $this->zipFile = $zipFile;
        $this->filename = realpath($filename) ?: $filename;
        $this->readOnly = $rdOnly;
        $this->status = self::ER_OK;
        $this->updatePublicProperties();

        return true;
    }

    /**
     * Saves and closes the open ZIP archive.
     * Unless the archive was opened in read-only mode, this method saves all pending changes
     * (added files, deletions, renames, comment updates) to the archive file on disk, then
     * releases the archive handle. After calling close(), you must call open() again before
     * doing any further work with the archive.
     *
     * @return bool Returns true on success, false if saving or closing failed
     * @see \ZipArchive::open()
     */
    public function close(): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            if (!$this->readOnly) {
                $this->zipFile->saveAsFile($this->filename);
            }
            $this->zipFile->close();
        } catch (Throwable) {
            return false;
        } finally {
            $this->zipFile = null;
            $this->filename = '';
            $this->numFiles = 0;
            $this->comment = '';
            $this->lastId = -1;
            $this->readOnly = false;
            $this->globalPassword = '';
        }

        return true;
    }

    /**
     * Returns the number of files in the open archive.
     * This method satisfies PHP's Countable interface, so you can also use
     * count($zip) instead of calling it directly.
     *
     * @return int The number of entries in the archive, or 0 if the archive is not open
     */
    public function count(): int
    {
        return $this->zipFile !== null ? count($this->zipFile->getEntries()) : 0;
    }

    /**
     * Adds a file from the filesystem to the archive.
     * The file at $filepath is read from disk and stored in the archive under $entryname.
     * If $entryname is empty, the base name of $filepath is used. When $start and $length
     * are provided, only that byte range of the file is added (useful for splitting large
     * files). The $flags parameter accepts ZipArchive::FL_OVERWRITE (default) to replace
     * an existing entry with the same name.
     *
     * @param string $filepath Path to the file on the filesystem to add
     * @param string $entryname Name to store the file under in the archive (default: basename of $filepath)
     * @param int $start Byte offset to start reading from (default: 0)
     * @param int $length Number of bytes to read (default: ZipArchive::LENGTH_TO_END = entire file)
     * @param int $flags ZipArchive::FL_OVERWRITE to replace existing entries (default: FL_OVERWRITE)
     * @return bool Returns true on success, false on failure
     * @see \ZipArchive::addFromString()
     * @see \ZipArchive::addEmptyDir()
     */
    public function addFile(
        string $filepath,
        string $entryname = '',
        int $start = 0,
        int $length = self::LENGTH_TO_END,
        int $flags = self::FL_OVERWRITE
    ): bool {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        $entryname = $entryname !== '' ? $entryname : basename($filepath);

        try {
            if ($start > 0 || $length !== self::LENGTH_TO_END) {
                $content =
                    $length !== self::LENGTH_TO_END ? file_get_contents($filepath, false, null, $start, $length) :
                        file_get_contents($filepath, false, null, $start);

                if ($content === false) {
                    return false;
                }

                $this->zipFile->addFromString($entryname, $content);
            } else {
                $this->zipFile->addFile($filepath, $entryname);
            }

            $this->updatePublicProperties();
            $this->lastId = (int) array_search($entryname, array_keys($this->zipFile->getEntries()), true);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Adds an entry to the archive using a string as the file contents.
     * This is the quickest way to add in-memory content to an archive without needing a
     * temporary file on disk. The string $content is stored in the archive under $name.
     * Use FL_OVERWRITE (the default) to replace an existing entry with the same name.
     *
     * @param string $name Name to store the entry under in the archive
     * @param string $content The string content to store
     * @param int $flags ZipArchive::FL_OVERWRITE to replace an existing entry (default: FL_OVERWRITE)
     * @return bool Returns true on success, false on failure
     * @see \ZipArchive::addFile()
     * @see \ZipArchive::addEmptyDir()
     */
    public function addFromString(string $name, string $content, int $flags = self::FL_OVERWRITE): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->addFromString($name, $content);
            $this->updatePublicProperties();
            $this->lastId = (int) array_search($name, array_keys($this->zipFile->getEntries()), true);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Adds an empty directory entry to the archive.
     * Creates a directory record inside the archive. Directories themselves contain no data,
     * but they serve as containers to organise other entries. A trailing slash is appended to
     * $dirname automatically if it is missing.
     *
     * @param string $dirname The name of the directory to add
     * @param int $flags Reserved for future use (default: 0)
     * @return bool Returns true on success, false on failure
     * @see \ZipArchive::addFromString()
     * @see \ZipArchive::addFile()
     */
    public function addEmptyDir(string $dirname, int $flags = 0): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->addEmptyDir(rtrim($dirname, '/') . '/');
            $this->updatePublicProperties();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Adds files matching a glob pattern to the archive.
     * Expands the $pattern using PHP's glob() function and adds every matching regular file to
     * the archive. Directory entries in the match are skipped. The $options array controls how
     * entry names are constructed inside the archive:
     * - 'add_path' (string) — prepend this path to every entry name
     * - 'remove_path' (string) — strip this prefix from every entry name
     * - 'remove_all_path' (bool) — strip all path components, keeping only the file name
     *
     * @param string $pattern A glob() pattern used to select the files to add
     * @param int $flags Bitfield of glob() flags (GLOB_* constants from PHP's glob function)
     * @param array $options Options controlling entry naming: add_path, remove_path, remove_all_path
     * @return array|false Returns an array of added entry names on success, false on failure
     * @see \ZipArchive::addPattern()
     * @see \ZipArchive::addFile()
     */
    public function addGlob(string $pattern, int $flags = 0, array $options = []): array|false
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        $files = glob($pattern, $flags);

        if ($files === false) {
            return false;
        }

        $addPath = (string) ($options['add_path'] ?? '');
        $removePath = (string) ($options['remove_path'] ?? '');
        $removeAllPath = (bool) ($options['remove_all_path'] ?? false);
        $addedNames = [];

        foreach ($files as $filepath) {
            if (is_dir($filepath)) {
                continue;
            }

            $entryname = $this->buildEntryName($filepath, $addPath, $removePath, $removeAllPath);

            try {
                $this->zipFile->addFile($filepath, $entryname);
                $addedNames[] = $entryname;
            } catch (Throwable) {
            }
        }

        $this->updatePublicProperties();

        return $addedNames;
    }

    /**
     * Adds files matching a PCRE regular expression to the archive.
     * Recursively scans $path and adds every file whose name matches $pattern (a PCRE regular
     * expression such as '/\.php$/') to the archive. The $options array controls entry naming
     * in the same way as addGlob(): add_path, remove_path, remove_all_path.
     *
     * @param string $pattern A PCRE regular expression to match file names against
     * @param string $path The directory to scan recursively (default: current directory)
     * @param array $options Options controlling entry naming: add_path, remove_path, remove_all_path
     * @return array|false Returns an array of added entry names on success, false on failure
     * @see \ZipArchive::addGlob()
     * @see \ZipArchive::addFile()
     */
    public function addPattern(string $pattern, string $path = '.', array $options = []): array|false
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        $addPath = (string) ($options['add_path'] ?? '');
        $removePath = (string) ($options['remove_path'] ?? '');
        $removeAllPath = (bool) ($options['remove_all_path'] ?? false);
        $addedNames = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (!preg_match($pattern, $file->getFilename())) {
                continue;
            }

            $filepath = $file->getPathname();
            $entryname = $this->buildEntryName($filepath, $addPath, $removePath, $removeAllPath);

            try {
                $this->zipFile->addFile($filepath, $entryname);
                $addedNames[] = $entryname;
            } catch (Throwable) {
            }
        }

        $this->updatePublicProperties();

        return $addedNames ?: false;
    }

    /**
     * Replaces the file at a given index with a new file from the filesystem.
     * Removes the entry at $index and adds a new entry from $filepath in its place, keeping
     * the same entry name. The $start and $length parameters allow adding only a byte range of
     * the replacement file (same semantics as addFile()).
     *
     * @param string $filepath Path to the replacement file on the filesystem
     * @param int $index Index of the entry to replace
     * @param int $start Byte offset to start reading $filepath from (default: 0)
     * @param int $length Number of bytes to read from $filepath (default: ZipArchive::LENGTH_TO_END)
     * @param int $flags Reserved (default: 0)
     * @return bool Returns true on success, false if the index does not exist or an error occurred
     * @see \ZipArchive::addFile()
     * @see \ZipArchive::deleteIndex()
     */
    public function replaceFile(
        string $filepath,
        int $index,
        int $start = 0,
        int $length = self::LENGTH_TO_END,
        int $flags = 0
    ): bool {
        $name = $this->resolveIndex($index);

        if ($name === false) {
            return false;
        }

        return $this->deleteName($name) && $this->addFile($filepath, $name, $start, $length);
    }


    /**
     * Deletes the entry at a given index from the archive.
     * The entry is removed from the archive when close() is called. The indices of entries
     * after the deleted one shift down by one.
     *
     * @param int $index The zero-based index of the entry to delete
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::deleteName()
     */
    public function deleteIndex(int $index): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->deleteName($name);
    }

    /**
     * Deletes the entry with the given name from the archive.
     * The entry is removed from the archive when close() is called.
     *
     * @param string $name The entry name to delete
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::deleteIndex()
     */
    public function deleteName(string $name): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->deleteFromName($name);
            $this->updatePublicProperties();

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Renames the entry at a given index.
     * The rename takes effect when close() is called.
     *
     * @param int $index The zero-based index of the entry to rename
     * @param string $new_name The new entry name
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::renameName()
     */
    public function renameIndex(int $index, string $new_name): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->renameName($name, $new_name);
    }

    /**
     * Renames the entry with the given name.
     * The rename takes effect when close() is called.
     *
     * @param string $name The current entry name
     * @param string $new_name The new entry name
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::renameIndex()
     */
    public function renameName(string $name, string $new_name): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->rename($name, $new_name);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Returns the contents of the entry at a given index as a string.
     * Reads and decompresses the entry at $index, returning its contents as a string. Use $len
     * to limit the number of bytes returned (0 means return all). Use ZipArchive::FL_UNCHANGED
     * in $flags to read the original, unmodified data even if the entry has been changed.
     *
     * @param int $index The zero-based index of the entry to read
     * @param int $len Maximum number of bytes to return (0 = entire entry)
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return string|false The entry contents on success, false if the index does not exist
     * @see \ZipArchive::getFromName()
     */
    public function getFromIndex(int $index, int $len = 0, int $flags = 0): string|false
    {
        $name = $this->resolveIndex($index);

        return $name !== false ? $this->getFromName($name, $len, $flags) : false;
    }

    /**
     * Returns the contents of the entry with the given name as a string.
     * Reads and decompresses the named entry, returning its contents. Use $len to limit the
     * number of bytes returned (0 means return all). Set ZipArchive::FL_NOCASE in $flags for
     * a case-insensitive name lookup.
     *
     * @param string $name The entry name to read
     * @param int $len Maximum number of bytes to return (0 = entire entry)
     * @param int $flags Bitfield of ZipArchive::FL_* constants; FL_NOCASE enables
     *                  case-insensitive lookup (default: 0)
     * @return string|false The entry contents on success, false if the entry does not exist
     * @see \ZipArchive::getFromIndex()
     * @see \ZipArchive::getStream()
     */
    public function getFromName(string $name, int $len = 0, int $flags = 0): string|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        $resolved = $this->resolveNameWithFlags($name, $flags);

        if ($resolved === false) {
            return false;
        }

        try {
            $contents = $this->zipFile->getEntryContents($resolved);

            return $len > 0 ? substr($contents, 0, $len) : $contents;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Returns a readable stream for the named entry.
     * Opens the entry as a PHP stream resource. Useful for reading large files without loading
     * the entire content into memory. Close the stream with fclose() when done.
     *
     * @param string $name The entry name to open as a stream
     * @return resource|false A stream resource on success, false if the entry does not exist
     * @see \ZipArchive::getFromName()
     */
    public function getStream(string $name): mixed
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            return $this->zipFile->getEntryStream($name);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Returns a readable stream for the entry at a given index.
     * Behaves identically to getStream(), but looks up the entry by its numeric index instead
     * of by name. Close the returned stream with fclose() when done.
     *
     * @param int $index The zero-based index of the entry to open as a stream
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return resource|false A stream resource on success, false if the index does not exist
     * @see \ZipArchive::getStreamName()
     * @see \ZipArchive::getStream()
     */
    public function getStreamIndex(int $index, int $flags = 0): mixed
    {
        $name = $this->resolveIndex($index);

        return $name !== false ? $this->getStream($name) : false;
    }

    /**
     * Returns a readable stream for the named entry (alias with flags).
     * Behaves identically to getStream(), with an additional $flags parameter for future
     * compatibility. Close the returned stream with fclose() when done.
     *
     * @param string $name The entry name to open as a stream
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return resource|false A stream resource on success, false if the entry does not exist
     * @see \ZipArchive::getStreamIndex()
     * @see \ZipArchive::getStream()
     */
    public function getStreamName(string $name, int $flags = 0): mixed
    {
        return $this->getStream($name);
    }


    /**
     * Returns the name of the entry at a given index.
     *
     * @param int $index The zero-based index of the entry
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return string|false The entry name on success, false if the index is out of range
     * @see \ZipArchive::statIndex()
     * @see \ZipArchive::locateName()
     */
    public function getNameIndex(int $index, int $flags = 0): string|false
    {
        return $this->resolveIndex($index);
    }

    /**
     * Finds the index of the entry with the given name.
     * Searches all entry names in the archive and returns the zero-based index of the first
     * match. Set ZipArchive::FL_NOCASE in $flags for a case-insensitive search.
     *
     * @param string $name The entry name to search for
     * @param int $flags ZipArchive::FL_NOCASE enables case-insensitive matching (default: 0)
     * @return int|false The zero-based index of the entry, or false if not found
     * @see \ZipArchive::statName()
     * @see \ZipArchive::getNameIndex()
     */
    public function locateName(string $name, int $flags = 0): int|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        $noCase = (bool) ($flags & self::FL_NOCASE);
        $compare = $noCase ? 'strcasecmp' : 'strcmp';

        foreach (array_keys($this->zipFile->getEntries()) as $index => $entryName) {
            if ($compare($entryName, $name) === 0) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Returns a stat array with metadata about the entry at a given index.
     * The returned array has the following keys: name, index, crc, size, mtime, comp_size,
     * comp_method, encryption_method. Equivalent to statName() but looks up by index.
     *
     * @param int $index The zero-based index of the entry
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return array|false An associative stat array on success, false if the index is out of range
     * @see \ZipArchive::statName()
     */
    public function statIndex(int $index, int $flags = 0): array|false
    {
        $name = $this->resolveIndex($index);

        return $name !== false ? $this->statName($name, $flags) : false;
    }

    /**
     * Returns a stat array with metadata about the named entry.
     * The returned array contains: name (string), index (int), crc (int), size (int —
     * uncompressed), mtime (int — Unix timestamp), comp_size (int — compressed), comp_method
     * (int — compression method integer from the ZIP spec), encryption_method (int — one of the
     * ZipArchive::EM_* constants).
     *
     * @param string $name The entry name
     * @param int $flags ZipArchive::FL_NOCASE enables case-insensitive lookup (default: 0)
     * @return array|false An associative stat array on success, false if the entry does not exist
     * @see \ZipArchive::locateName()
     * @see \ZipArchive::statIndex()
     */
    public function statName(string $name, int $flags = 0): array|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        $resolved = $this->resolveNameWithFlags($name, $flags);

        if ($resolved === false) {
            return false;
        }

        try {
            $entry = $this->zipFile->getEntry($resolved);
            $index = $this->locateName($resolved);

            return $this->buildStatArray($entry, $index !== false ? $index : 0);
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Returns the global comment of the archive.
     * ZIP archives may contain a global comment string. This method returns it, or an empty
     * string if no comment has been set.
     *
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return string|false The archive comment on success, false if the archive is not open
     * @see \ZipArchive::setArchiveComment()
     */
    public function getArchiveComment(int $flags = 0): string|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        return $this->zipFile->getArchiveComment() ?? '';
    }

    /**
     * Sets the global comment on the archive.
     * The comment is saved when close() is called.
     *
     * @param string $comment The comment string to set on the archive
     * @return bool Returns true on success, false if the archive is not open or is read-only
     * @see \ZipArchive::getArchiveComment()
     */
    public function setArchiveComment(string $comment): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->setArchiveComment($comment);
            $this->comment = $comment;

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Returns the comment on the entry at a given index.
     * Individual ZIP entries may carry their own comment strings separate from the archive
     * comment. This method retrieves the comment for the entry at $index.
     *
     * @param int $index The zero-based index of the entry
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return string|false The entry comment on success, false if the index does not exist
     * @see \ZipArchive::setCommentIndex()
     * @see \ZipArchive::getCommentName()
     */
    public function getCommentIndex(int $index, int $flags = 0): string|false
    {
        $name = $this->resolveIndex($index);

        return $name !== false ? $this->getCommentName($name, $flags) : false;
    }

    /**
     * Returns the comment on the named entry.
     *
     * @param string $name The entry name
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return string|false The entry comment on success, false if the entry does not exist
     * @see \ZipArchive::setCommentName()
     * @see \ZipArchive::getCommentIndex()
     */
    public function getCommentName(string $name, int $flags = 0): string|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            return $this->zipFile->getEntryComment($name);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Sets the comment on the entry at a given index.
     * The change is saved when close() is called.
     *
     * @param int $index The zero-based index of the entry
     * @param string $comment The comment string to set
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::getCommentIndex()
     * @see \ZipArchive::setCommentName()
     */
    public function setCommentIndex(int $index, string $comment): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->setCommentName($name, $comment);
    }

    /**
     * Sets the comment on the named entry.
     * The change is saved when close() is called.
     *
     * @param string $name The entry name
     * @param string $comment The comment string to set
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::getCommentName()
     * @see \ZipArchive::setCommentIndex()
     */
    public function setCommentName(string $name, string $comment): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->setEntryComment($name, $comment);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Sets the compression method on the entry at a given index.
     * Use one of the ZipArchive::CM_* constants for $method. ZipArchive::CM_DEFAULT uses
     * deflate compression. ZipArchive::CM_STORE disables compression entirely.
     *
     * @param int $index The zero-based index of the entry
     * @param int $method Compression method — one of the ZipArchive::CM_* constants
     * @param int $compflags Reserved compression flags (default: 0)
     * @return bool Returns true on success, false if the index does not exist or the method is not supported
     * @see \ZipArchive::setCompressionName()
     */
    public function setCompressionIndex(int $index, int $method, int $compflags = 0): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->setCompressionName($name, $method, $compflags);
    }

    /**
     * Sets the compression method on the named entry.
     * Use one of the ZipArchive::CM_* constants for $method. ZipArchive::CM_DEFAULT uses
     * deflate compression. ZipArchive::CM_STORE disables compression entirely.
     *
     * @param string $name The entry name
     * @param int $method Compression method — one of the ZipArchive::CM_* constants
     * @param int $compflags Reserved compression flags (default: 0)
     * @return bool Returns true on success, false if the entry does not exist or the method is not supported
     * @see \ZipArchive::setCompressionIndex()
     */
    public function setCompressionName(string $name, int $method, int $compflags = 0): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        $actualMethod = $method === self::CM_DEFAULT ? ZipCompressionMethod::DEFLATED : $method;

        try {
            $this->zipFile->setCompressionMethodEntry($name, $actualMethod);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Checks whether the given compression method is supported by this implementation.
     * Returns true for methods that nelexa/zip can read or write. Currently: CM_STORE (0),
     * CM_DEFLATE (8), and CM_BZIP2 (12) when the bz2 PHP extension is available.
     *
     * @param int $method The compression method constant to check
     * @param bool $enc Whether to check for encoding (compression) support as opposed to decoding (default: true)
     * @return bool Returns true if the method is supported, false otherwise
     * @see \ZipArchive::isEncryptionMethodSupported()
     */
    public static function isCompressionMethodSupported(int $method, bool $enc = true): bool
    {
        $supported = [ZipCompressionMethod::STORED, ZipCompressionMethod::DEFLATED];

        if (extension_loaded('bz2')) {
            $supported[] = ZipCompressionMethod::BZIP2;
        }

        return in_array($method, $supported, true);
    }


    /**
     * Sets the archive-wide password used to decrypt encrypted entries.
     * This password is used automatically when reading encrypted entries. It can be
     * overridden on a per-entry basis with setEncryptionName() or setEncryptionIndex().
     *
     * @param string $password The password to use for decryption
     * @return bool Returns true on success, false if the archive is not open
     * @see \ZipArchive::setEncryptionName()
     */
    public function setPassword(string $password): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $this->globalPassword = $password;
            $this->zipFile->setReadPassword($password);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Sets the encryption method and optional password on the entry at a given index.
     * The entry will be encrypted using the given method when the archive is saved. Use one of
     * the ZipArchive::EM_* constants for $method. If $password is null, the archive-wide
     * password set by setPassword() is used.
     *
     * @param int $index The zero-based index of the entry to encrypt
     * @param int $method Encryption method — one of the ZipArchive::EM_* constants
     * @param string|null $password The password to use (null falls back to the archive password)
     * @return bool Returns true on success, false if the index does not exist or the method is unsupported
     * @see \ZipArchive::setEncryptionName()
     * @see \ZipArchive::setPassword()
     */
    public function setEncryptionIndex(int $index, int $method, ?string $password = null): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->setEncryptionName($name, $method, $password);
    }

    /**
     * Sets the encryption method and optional password on the named entry.
     * The entry will be encrypted using the given method when the archive is saved. Use one of
     * the ZipArchive::EM_* constants for $method. If $password is null, the archive-wide
     * password set by setPassword() is used.
     *
     * @param string $name The entry name to encrypt
     * @param int $method Encryption method — one of the ZipArchive::EM_* constants
     * @param string|null $password The password to use (null falls back to the archive password)
     * @return bool Returns true on success, false if the entry does not exist or the method is unsupported
     * @see \ZipArchive::setEncryptionIndex()
     * @see \ZipArchive::setPassword()
     */
    public function setEncryptionName(string $name, int $method, ?string $password = null): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        $effectivePassword = $password ?? $this->globalPassword;

        if ($effectivePassword === '') {
            return false;
        }

        try {
            $zipFileMethod = $this->mapEncryptionMethodToZipFile($method);
            $this->zipFile->setPasswordEntry($name, $effectivePassword, $zipFileMethod);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Checks whether the given encryption method is supported by this implementation.
     * Returns true for EM_NONE, EM_TRAD_PKWARE, EM_AES_128, EM_AES_192, and EM_AES_256.
     *
     * @param int $method The encryption method constant to check
     * @param bool $enc Whether to check for encryption support as opposed to decryption (default: true)
     * @return bool Returns true if the method is supported, false otherwise
     * @see \ZipArchive::isCompressionMethodSupported()
     */
    public static function isEncryptionMethodSupported(int $method, bool $enc = true): bool
    {
        return in_array($method, [
            self::EM_NONE,
            self::EM_TRAD_PKWARE,
            self::EM_AES_128,
            self::EM_AES_192,
            self::EM_AES_256,
        ], true);
    }


    /**
     * Retrieves the OS and external attributes of the entry at a given index.
     * External attributes encode file permissions and other OS-specific metadata. The $opsys
     * output parameter receives one of the ZipArchive::OPSYS_* constants, and $attr receives
     * the raw attribute value (e.g. Unix file mode bits shifted left by 16).
     *
     * @param int $index The zero-based index of the entry
     * @param int $opsys Output: receives one of the ZipArchive::OPSYS_* constants
     * @param int $attr Output: receives the raw external attributes integer
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::setExternalAttributesIndex()
     * @see \ZipArchive::getExternalAttributesName()
     */
    public function getExternalAttributesIndex(int $index, int &$opsys, int &$attr, int $flags = 0): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->getExternalAttributesName($name, $opsys, $attr, $flags);
    }

    /**
     * Retrieves the OS and external attributes of the named entry.
     * The $opsys output parameter receives one of the ZipArchive::OPSYS_* constants, and
     * $attr receives the raw attribute value.
     *
     * @param string $name The entry name
     * @param int $opsys Output: receives one of the ZipArchive::OPSYS_* constants
     * @param int $attr Output: receives the raw external attributes integer
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::setExternalAttributesName()
     * @see \ZipArchive::getExternalAttributesIndex()
     */
    public function getExternalAttributesName(string $name, int &$opsys, int &$attr, int $flags = 0): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $entry = $this->zipFile->getEntry($name);
            $opsys = $entry->getCreatedOS();
            $attr = $entry->getExternalAttributes();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Sets the OS and external attributes of the entry at a given index.
     * Use the ZipArchive::OPSYS_* constants for $opsys and the appropriate attribute bits for
     * $attr (e.g. Unix mode << 16 for OPSYS_UNIX). Changes take effect when close() is called.
     *
     * @param int $index The zero-based index of the entry
     * @param int $opsys One of the ZipArchive::OPSYS_* constants
     * @param int $attr The raw external attributes value to set
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::getExternalAttributesIndex()
     * @see \ZipArchive::setExternalAttributesName()
     */
    public function setExternalAttributesIndex(int $index, int $opsys, int $attr, int $flags = 0): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->setExternalAttributesName($name, $opsys, $attr, $flags);
    }

    /**
     * Sets the OS and external attributes of the named entry.
     * Use the ZipArchive::OPSYS_* constants for $opsys and the appropriate attribute bits for
     * $attr (e.g. Unix mode << 16 for OPSYS_UNIX). Changes take effect when close() is called.
     *
     * @param string $name The entry name
     * @param int $opsys One of the ZipArchive::OPSYS_* constants
     * @param int $attr The raw external attributes value to set
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::getExternalAttributesName()
     * @see \ZipArchive::setExternalAttributesIndex()
     */
    public function setExternalAttributesName(string $name, int $opsys, int $attr, int $flags = 0): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $entry = $this->zipFile->getEntry($name);
            $entry->setCreatedOS($opsys)->setExternalAttributes($attr);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Sets the modification time on the entry at a given index.
     * The timestamp is stored in the ZIP central directory and local header. Changes take
     * effect when close() is called.
     *
     * @param int $index The zero-based index of the entry
     * @param int $timestamp Unix timestamp to set as the modification time
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::setMtimeName()
     */
    public function setMtimeIndex(int $index, int $timestamp, int $flags = 0): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->setMtimeName($name, $timestamp, $flags);
    }

    /**
     * Sets the modification time on the named entry.
     * The timestamp is stored in the ZIP central directory and local header. Changes take
     * effect when close() is called.
     *
     * @param string $name The entry name
     * @param int $timestamp Unix timestamp to set as the modification time
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::setMtimeIndex()
     */
    public function setMtimeName(string $name, int $timestamp, int $flags = 0): bool
    {
        if ($this->zipFile === null || $this->readOnly) {
            return false;
        }

        try {
            $this->zipFile->getEntry($name)->setTime($timestamp);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Reverts all pending changes made to the archive since it was opened.
     * All entries and the archive comment are restored to the state they were in when the
     * archive was opened.
     *
     * @return bool Returns true on success, false if the archive is not open
     * @see \ZipArchive::unchangeName()
     * @see \ZipArchive::unchangeArchive()
     */
    public function unchangeAll(): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $this->zipFile->unchangeAll();
            $this->updatePublicProperties();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reverts any pending change to the archive's global comment.
     *
     * @return bool Returns true on success, false if the archive is not open
     * @see \ZipArchive::unchangeAll()
     */
    public function unchangeArchive(): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $this->zipFile->unchangeArchiveComment();
            $this->updatePublicProperties();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reverts all pending changes to the entry at a given index.
     *
     * @param int $index The zero-based index of the entry to restore
     * @return bool Returns true on success, false if the index does not exist
     * @see \ZipArchive::unchangeName()
     * @see \ZipArchive::unchangeAll()
     */
    public function unchangeIndex(int $index): bool
    {
        $name = $this->resolveIndex($index);

        return $name !== false && $this->unchangeName($name);
    }

    /**
     * Reverts all pending changes to the named entry.
     *
     * @param string $name The entry name to restore
     * @return bool Returns true on success, false if the entry does not exist
     * @see \ZipArchive::unchangeIndex()
     * @see \ZipArchive::unchangeAll()
     */
    public function unchangeName(string $name): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $this->zipFile->unchangeEntry($name);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Extracts entries from the archive to a directory on disk.
     * When $files is null, all entries are extracted. When $files is a string, only that single
     * entry is extracted. When $files is an array of strings, only those entries are extracted.
     * The directory $pathto is created automatically if it does not exist.
     *
     * @param string $pathto Destination directory path
     * @param array|string|null $files Entry name or list of entry names to extract (null = all)
     * @return bool Returns true on success, false on failure
     */
    public function extractTo(string $pathto, array|string|null $files = null): bool
    {
        if ($this->zipFile === null) {
            return false;
        }

        try {
            $entryList = is_string($files) ? [$files] : $files;
            $this->zipFile->extractTo($pathto, $entryList);

            return true;
        } catch (Throwable) {
            return false;
        }
    }


    /**
     * Returns a human-readable description of the last error that occurred.
     * Maps the current value of the $status property to an English error message. Useful for
     * displaying error information when open() or another method fails.
     *
     * @return string A plain-English description of the last error, or 'No error' if none
     * @see \ZipArchive::clearError()
     */
    public function getStatusString(): string
    {
        return match ($this->status) {
            self::ER_OK => 'No error',
            self::ER_MULTIDISK => 'Multi-disk zip archives not supported',
            self::ER_RENAME => 'Renaming temporary file failed',
            self::ER_CLOSE => 'Closing zip archive failed',
            self::ER_SEEK => 'Seek error',
            self::ER_READ => 'Read error',
            self::ER_WRITE => 'Write error',
            self::ER_CRC => 'CRC error',
            self::ER_ZIPCLOSED => 'Containing zip archive was closed',
            self::ER_NOENT => 'No such file',
            self::ER_EXISTS => 'File already exists',
            self::ER_OPEN => 'Can\'t open file',
            self::ER_TMPOPEN => 'Failure to create temporary file',
            self::ER_ZLIB => 'Zlib error',
            self::ER_MEMORY => 'Memory allocation failure',
            self::ER_CHANGED => 'Entry has been changed',
            self::ER_COMPNOTSUPP => 'Compression method not supported',
            self::ER_EOF => 'Premature end of file',
            self::ER_INVAL => 'Invalid argument',
            self::ER_NOZIP => 'Not a zip archive',
            self::ER_INTERNAL => 'Internal error',
            self::ER_INCONS => 'Zip archive inconsistent',
            self::ER_REMOVE => 'Can\'t remove file',
            self::ER_DELETED => 'Entry has been deleted',
            self::ER_ENCRNOTSUPP => 'Encryption method not supported',
            self::ER_RDONLY => 'Read-only archive',
            self::ER_NOPASSWD => 'No password provided',
            self::ER_WRONGPASSWD => 'Wrong password provided',
            self::ER_OPNOTSUPP => 'Operation not supported',
            self::ER_INUSE => 'Resource still in use',
            self::ER_TELL => 'Tell error',
            self::ER_COMPRESSED_DATA => 'Compressed data invalid',
            self::ER_CANCELLED => 'Operation cancelled',
            default => 'Unknown error',
        };
    }

    /**
     * Clears the last error code recorded in the $status property.
     * Resets both $status and $statusSys to 0 (ER_OK). Call this after handling an error if
     * you want to continue using the archive object.
     *
     * @see \ZipArchive::getStatusString()
     */
    public function clearError(): void
    {
        $this->status = self::ER_OK;
        $this->statusSys = 0;
    }

    /**
     * Returns the current value of an archive-level flag.
     * Note: Archive-level flags are a libzip internal concept and cannot be accessed through
     * nelexa/zip. This method always returns 0.
     *
     * @param int $flag The archive flag to query (one of ZipArchive::AFL_* constants)
     * @param int $flags Bitfield of ZipArchive::FL_* constants (default: 0)
     * @return int The flag value; always 0 in this polyfill
     * @see \ZipArchive::setArchiveFlag()
     */
    public function getArchiveFlag(int $flag, int $flags = 0): int
    {
        return 0;
    }

    /**
     * Sets an archive-level flag.
     * Note: Archive-level flags are a libzip internal concept and cannot be set through
     * nelexa/zip. This method always returns false.
     *
     * @param int $flag The archive flag to set (one of ZipArchive::AFL_* constants)
     * @param int $value The value to assign (0 = clear, 1 = set)
     * @return bool Always returns false in this polyfill
     * @see \ZipArchive::getArchiveFlag()
     */
    public function setArchiveFlag(int $flag, int $value): bool
    {
        return false;
    }

    /**
     * Registers a progress callback that is called periodically during long operations.
     * Note: nelexa/zip does not support progress callbacks. This method always returns false.
     *
     * @param float $rate How often the callback is called, as a fraction of work done (0.0–1.0)
     * @param callable $callback A function called with a float progress value between 0.0 and 1.0.
     *  The callback has the signature `function (float $rate): void`
     * @return bool Always returns false in this polyfill
     */
    public function registerProgressCallback(float $rate, callable $callback): bool
    {
        return false;
    }

    /**
     * Registers a cancel callback that is called periodically and can abort the operation.
     * Note: nelexa/zip does not support cancellation callbacks. This method always returns false.
     *
     * @param callable $callback A function that returns 0 to continue or non-zero to cancel the operation.
     *  The callback has the signature `function (): int`
     * @return bool Always returns false in this polyfill
     */
    public function registerCancelCallback(callable $callback): bool
    {
        return false;
    }


    /**
     * Resolves a numeric index to an entry name.
     * Fetches the ordered list of entry names from the open archive and returns the one
     * at the given position. Returns false when the index is out of range or no archive
     * is open. Used internally by all index-based public methods.
     *
     * @param int $index Zero-based entry index
     * @return string|false The entry name, or false if out of range or archive is closed
     */
    private function resolveIndex(int $index): string|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        $names = array_keys($this->zipFile->getEntries());

        return $names[$index] ?? false;
    }

    /**
     * Resolves an entry name, optionally applying case-insensitive matching.
     * When FL_NOCASE is set in $flags, the method walks all entry names and returns the first
     * one that matches case-insensitively. When the flag is not set, a fast direct lookup via
     * ZipFile::hasEntry() is used. Returns false when the name is not found or no archive
     * is open. Used internally by getFromName(), statName(), and similar name-based methods.
     *
     * @param string $name The entry name to look up
     * @param int $flags Bitfield of ZipArchive::FL_* constants
     * @return string|false The resolved (exact) entry name, or false if not found
     */
    private function resolveNameWithFlags(string $name, int $flags): string|false
    {
        if ($this->zipFile === null) {
            return false;
        }

        if ($this->zipFile->hasEntry($name)) {
            return $name;
        }

        if ($flags & self::FL_NOCASE) {
            $lowerName = strtolower($name);

            foreach (array_keys($this->zipFile->getEntries()) as $entryName) {
                if (strtolower($entryName) === $lowerName) {
                    return $entryName;
                }
            }
        }

        return false;
    }

    /**
     * Builds the 8-key stat array from a ZipEntry object.
     * Converts a nelexa/zip ZipEntry model into the associative array format that the
     * native ZipArchive::statName() and ZipArchive::statIndex() methods return. The
     * array always contains the keys: name, index, crc, size, mtime, comp_size,
     * comp_method, and encryption_method.
     *
     * @param ZipEntry $entry The nelexa/zip entry model to read metadata from
     * @param int $index The zero-based index of the entry in the archive
     * @return array Associative stat array with 8 keys
     */
    private function buildStatArray(ZipEntry $entry, int $index): array
    {
        return [
            'name' => $entry->getName(),
            'index' => $index,
            'crc' => $entry->getCrc(),
            'size' => $entry->getUncompressedSize(),
            'mtime' => $entry->getTime(),
            'comp_size' => $entry->getCompressedSize(),
            'comp_method' => $entry->getCompressionMethod(),
            'encryption_method' => $this->mapEncryptionMethodFromZipFile($entry->getEncryptionMethod()),
        ];
    }

    /**
     * Maps a nelexa/zip ZipEncryptionMethod constant to the corresponding ZipArchive EM_* constant.
     * nelexa/zip and the native ZipArchive class use different integer values for the
     * same encryption methods. This helper translates from the nelexa representation
     * (returned by ZipEntry::getEncryptionMethod()) to the ZipArchive representation
     * (returned in the stat array and used as public API values). Unknown methods map to
     * EM_NONE.
     *
     * @param int $zipFileMethod A ZipEncryptionMethod::* constant value from nelexa/zip
     * @return int The corresponding ZipArchive::EM_* constant value
     */
    private function mapEncryptionMethodFromZipFile(int $zipFileMethod): int
    {
        return match ($zipFileMethod) {
            ZipEncryptionMethod::PKWARE => self::EM_TRAD_PKWARE,
            ZipEncryptionMethod::WINZIP_AES_128 => self::EM_AES_128,
            ZipEncryptionMethod::WINZIP_AES_192 => self::EM_AES_192,
            ZipEncryptionMethod::WINZIP_AES_256 => self::EM_AES_256,
            default => self::EM_NONE,
        };
    }

    /**
     * Maps a ZipArchive EM_* constant to the corresponding nelexa/zip ZipEncryptionMethod constant.
     * The inverse of mapEncryptionMethodFromZipFile(). Translates from the public
     * ZipArchive::EM_* values (received via setEncryptionName() or setEncryptionIndex())
     * to the internal ZipEncryptionMethod::* constants expected by nelexa/zip's
     * ZipFile::setPasswordEntry(). Unknown or unencrypted values map to
     * ZipEncryptionMethod::NONE.
     *
     * @param int $emConst A ZipArchive::EM_* constant value
     * @return int The corresponding ZipEncryptionMethod::* constant value from nelexa/zip
     */
    private function mapEncryptionMethodToZipFile(int $emConst): int
    {
        return match ($emConst) {
            self::EM_TRAD_PKWARE => ZipEncryptionMethod::PKWARE,
            self::EM_AES_128 => ZipEncryptionMethod::WINZIP_AES_128,
            self::EM_AES_192 => ZipEncryptionMethod::WINZIP_AES_192,
            self::EM_AES_256 => ZipEncryptionMethod::WINZIP_AES_256,
            default => ZipEncryptionMethod::NONE,
        };
    }

    /**
     * Builds an entry name from a file path, applying add/remove path options.
     * Used by addGlob() and addPattern() to transform filesystem paths into archive
     * entry names according to the standard options array. When $removeAllPath is true,
     * only the base name of the file is kept. Otherwise, $removePath is stripped from
     * the left of the path and $addPath is prepended. The resulting name always has any
     * leading slash removed so entries are stored without an absolute path prefix.
     *
     * @param string $filepath Absolute or relative path to the file on disk
     * @param string $addPath Prefix to prepend to every constructed entry name
     * @param string $removePath Prefix to strip from the file path before adding $addPath
     * @param bool $removeAllPath When true, discard all path components and use only the base name
     * @return string The constructed entry name ready to be stored in the archive
     */
    private function buildEntryName(
        string $filepath,
        string $addPath,
        string $removePath,
        bool $removeAllPath
    ): string {
        if ($removeAllPath) {
            return ltrim($addPath . basename($filepath), '/');
        }

        $entryname = $filepath;

        if ($removePath !== '' && str_starts_with($entryname, $removePath)) {
            $entryname = substr($entryname, strlen($removePath));
        }

        return ltrim($addPath . $entryname, '/');
    }

    /**
     * Synchronises the public $numFiles and $comment properties from the live ZipFile state.
     * Called after every operation that may change the number of entries or the archive
     * comment (addFile, addFromString, deleteName, unchangeAll, etc.). Keeps the public
     * properties consistent so callers can read them without calling count() separately.
     * Does nothing when no archive is open.
     */
    private function updatePublicProperties(): void
    {
        if ($this->zipFile === null) {
            return;
        }

        $this->numFiles = count($this->zipFile->getEntries());
        $this->comment = $this->zipFile->getArchiveComment() ?? '';
    }
}
