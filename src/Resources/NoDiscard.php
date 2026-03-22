<?php

declare(strict_types=1);

use Attribute;

if (\PHP_VERSION_ID < 80500) {
    /**
     * NoDiscard attribute stub class for PHP 8.5.
     *
     * This stub attribute exists for type compatibility with PHP 8.5+ code.
     * On PHP versions < 8.0, attributes are not supported by the parser,
     * so this class cannot be used as an actual attribute. However, it
     * provides:
     * 1. Type hints in code that will run on PHP 8.0+
     * 2. Reflection checks for attribute existence
     * 3. Forward compatibility when code runs on PHP 8.5+
     *
     * The NoDiscard attribute indicates that the return value of a function
     * or method should not be discarded. PHP can optionally emit a warning
     * when the return value is ignored.
     *
     * @see https://wiki.php.net/rfc/no_discard_attribute
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    #[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
    // phpcs:ignore
    final class NoDiscard
    {
        /**
         * Constructor for the NoDiscard attribute.
         *
         * In PHP 8.5+, this attribute accepts an optional string for
         * customization of the warning message. This stub accepts
         * the same for compatibility.
         *
         * @param string|null $message The message to display if the return value is discarded (optional)
         */
        public function __construct(public ?string $message = null)
        {
        }
    }
}
