<?php

declare(strict_types=1);

use Attribute;

if (\PHP_VERSION_ID < 80400) {
    /**
     * Deprecated attribute stub class for PHP 8.4.
     *
     * This stub attribute exists for type compatibility with PHP 8.4+ code.
     * On PHP versions < 8.0, attributes are not supported by the parser,
     * so this class cannot be used as an actual attribute. However, it
     * provides:
     * 1. Type hints in code that will run on PHP 8.0+
     * 2. Reflection checks for attribute existence
     * 3. Forward compatibility when code runs on PHP 8.4+
     *
     * The Deprecated attribute indicates that a function, method, class,
     * or other code element is deprecated and should not be used anymore.
     * PHP can optionally emit warnings when deprecated code is used.
     *
     * @see https://wiki.php.net/rfc/deprecated_attribute
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    #[Attribute(
        Attribute::TARGET_CLASS |
        Attribute::TARGET_METHOD |
        Attribute::TARGET_FUNCTION |
        Attribute::TARGET_PROPERTY |
        Attribute::TARGET_CLASS_CONSTANT |
        Attribute::TARGET_PARAMETER |
        // TARGET_VARIABLE (128) is only available in PHP 8.4+
        // We use 128 directly for forward compatibility
        128
    )]
    // phpcs:ignore
    final class Deprecated
    {
        /**
         * Constructor for the Deprecated attribute.
         *
         * In PHP 8.4+, this attribute accepts optional parameters for
         * customization of the deprecation message. This stub accepts
         * the same for compatibility.
         *
         * @param string|null $message The deprecation message (optional)
         * @param string|null $since The version since when it's deprecated (optional)
         * @param bool $emit Whether to emit a deprecation warning (optional)
         */
        public function __construct(
            public ?string $message = null,
            public ?string $since = null,
            public bool $emit = true
        ) {
        }
    }
}
