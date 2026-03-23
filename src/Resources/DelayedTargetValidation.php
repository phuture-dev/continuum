<?php

declare(strict_types=1);

use Attribute;

if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 80500) {
    /**
     * DelayedTargetValidation attribute stub class for PHP 8.5.
     *
     * This stub attribute exists for type compatibility with PHP 8.5+ code.
     * On PHP versions < 8.0, attributes are not supported by the parser,
     * so this class cannot be used as an actual attribute. However, it
     * provides:
     * 1. Type hints in code that will run on PHP 8.0+
     * 2. Reflection checks for attribute existence
     * 3. Forward compatibility when code runs on PHP 8.5+
     *
     * The DelayedTargetValidation attribute indicates that attribute target
     * validation should be delayed until the attribute is actually used,
     * rather than at the time of declaration. This is useful for attributes
     * that may be used in contexts where the target is not yet known.
     *
     * @see https://wiki.php.net/rfc/delayed_attribute_target_validation
     *
     * @copyright Copyright (c) 2026, Advandz Technologies, LLC
     * @license https://opensource.org/licenses/MIT MIT License
     * @link https://www.phuture.dev/ Phuture
     */
    #[Attribute(Attribute::TARGET_CLASS)]
    // phpcs:ignore
    final class DelayedTargetValidation
    {
        /**
         * Constructor for the DelayedTargetValidation attribute.
         */
        public function __construct()
        {
        }
    }
}
