<?php

declare(strict_types=1);

use Attribute;

if (\PHP_VERSION_ID < 80300) {
    /**
     * Override attribute stub class for PHP 8.3.
     *
     * This stub attribute exists for type compatibility with PHP 8.3+ code.
     * On PHP versions < 8.0, attributes are not supported by the parser,
     * so this class cannot be used as an actual attribute. However, it
     * provides:
     * 1. Type hints in code that will run on PHP 8.0+
     * 2. Reflection checks for attribute existence
     * 3. Forward compatibility when code runs on PHP 8.3+
     *
     * The Override attribute indicates that a method from a parent class
     * is being intentionally overridden. This allows PHP to verify that
     * the parent method actually exists, helping to catch typos or
     * refactoring errors.
     *
     * @see https://wiki.php.net/rfc/marking_overriden_methods
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
        Attribute::TARGET_PARAMETER
    )]
    // phpcs:ignore
    final class Override
    {
        /**
         * Constructor for the Override attribute.
         *
         * In PHP 8.3+, this attribute accepts an optional string specifying
         * the method name being overridden. This stub accepts the same for
         * compatibility.
         *
         * @param string|null $methodName The method name being overridden (optional)
         */
        public function __construct(public ?string $methodName = null)
        {
        }
    }
}
