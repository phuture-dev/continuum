<?php

declare(strict_types=1);

/**
 * RoundingMode enum stub for PHP 8.4.
 *
 * The RoundingMode enum defines the rounding modes available for
 * mathematical operations like round() and bcround().
 *
 * @see https://www.php.net/manual/en/enum.roundingmode.php
 * @see https://wiki.php.net/rfc/rounding
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
// phpcs:ignore
enum RoundingMode
{
    case HalfAwayFromZero;
    case HalfTowardsZero;
    case HalfEven;
    case HalfOdd;
    case PositiveInfinity;
    case NegativeInfinity;
    case TowardsZero;
    case AwayFromZero;
}
