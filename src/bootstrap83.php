<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

use Phuture\Continuum\Php83;

if (\PHP_VERSION_ID >= 80300) {
    return;
}

/**
 * PHP 8.3 constants
 */
if (extension_loaded('posix')) {
    if (!defined('POSIX_PC_LINK_MAX')) {
        define('POSIX_PC_LINK_MAX', Php83::POSIX_PC_LINK_MAX);
    }

    if (!defined('POSIX_PC_MAX_CANON')) {
        define('POSIX_PC_MAX_CANON', Php83::POSIX_PC_MAX_CANON);
    }

    if (!defined('POSIX_PC_MAX_INPUT')) {
        define('POSIX_PC_MAX_INPUT', Php83::POSIX_PC_MAX_INPUT);
    }

    if (!defined('POSIX_PC_NAME_MAX')) {
        define('POSIX_PC_NAME_MAX', Php83::POSIX_PC_NAME_MAX);
    }

    if (!defined('POSIX_PC_PATH_MAX')) {
        define('POSIX_PC_PATH_MAX', Php83::POSIX_PC_PATH_MAX);
    }

    if (!defined('POSIX_PC_PIPE_BUF')) {
        define('POSIX_PC_PIPE_BUF', Php83::POSIX_PC_PIPE_BUF);
    }

    if (!defined('POSIX_PC_CHOWN_RESTRICTED')) {
        define('POSIX_PC_CHOWN_RESTRICTED', Php83::POSIX_PC_CHOWN_RESTRICTED);
    }

    if (!defined('POSIX_PC_NO_TRUNC')) {
        define('POSIX_PC_NO_TRUNC', Php83::POSIX_PC_NO_TRUNC);
    }

    if (!defined('POSIX_PC_ALLOC_SIZE_MIN')) {
        define('POSIX_PC_ALLOC_SIZE_MIN', Php83::POSIX_PC_ALLOC_SIZE_MIN);
    }

    if (!defined('POSIX_PC_SYMLINK_MAX')) {
        define('POSIX_PC_SYMLINK_MAX', Php83::POSIX_PC_SYMLINK_MAX);
    }

    if (!defined('POSIX_F_OK')) {
        define('POSIX_F_OK', Php83::POSIX_F_OK);
    }

    if (!defined('POSIX_X_OK')) {
        define('POSIX_X_OK', Php83::POSIX_X_OK);
    }

    if (!defined('POSIX_W_OK')) {
        define('POSIX_W_OK', Php83::POSIX_W_OK);
    }

    if (!defined('POSIX_R_OK')) {
        define('POSIX_R_OK', Php83::POSIX_R_OK);
    }
}

/**
 * PHP 8.3 functions
 */
if (extension_loaded('posix')) {
    if (!function_exists('posix_eaccess')) {
        /**
         * Checks user's permissions for a file using effective ID.
         *
         * @param string $filename The file to check
         * @param int $mode The mode to check (F_OK, R_OK, W_OK, X_OK)
         * @return bool Returns true if access is allowed, false otherwise
         */
        function posix_eaccess(string $filename, int $mode = 0): bool
        {
            return Php83::posix_eaccess($filename, $mode);
        }
    }
}
