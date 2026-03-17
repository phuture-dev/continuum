<?php

declare(strict_types=1);

namespace IMAP;

if (\PHP_VERSION_ID < 80100 && extension_loaded('imap') && !class_exists(\IMAP\Connection::class)) {
    /**
     * IMAP\Connection stub class for PHP 8.0 polyfill.
     *
     * This stub class exists for type compatibility with PHP 8.1+ code.
     * In PHP 8.1+, the IMAP extension was updated to use an IMAP\Connection
     * object instead of a resource. This stub provides:
     * 1. Type hints in code that will run on PHP 8.0
     * 2. Instanceof checks for IMAP connection validation
     * 3. Forward compatibility when code runs on PHP 8.1+
     *
     * On PHP 8.1+, the native IMAP\Connection class is final and cannot be
     * extended. This stub should only be used for type-hinting and instanceof
     * checks in polyfill code that needs to support PHP 8.0.
     *
     * @see https://wiki.php.net/rfc/imap_connection_resource_to_object
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    final class Connection
    {
        /**
         * Private constructor to prevent instantiation.
         * IMAP connections are created through imap_open() and similar functions,
         * not through direct instantiation.
         */
        private function __construct()
        {
        }
    }
}
