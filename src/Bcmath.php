<?php

declare(strict_types=1);

namespace Phuture\Continuum;

use ValueError;

/**
 * BC Math polyfill methods.
 *
 * This class provides static methods to polyfill BC Math functions when the
 * bcmath extension is not available. All methods delegate to native functions
 * when available, otherwise provide pure PHP implementations.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
final class Bcmath
{
    private static ?int $scale = null;

    /**
     * Add two arbitrary precision numbers.
     *
     * @see https://www.php.net/manual/en/function.bcadd.php
     *
     * @param string $num1 The left operand, as a string
     * @param string $num2 The right operand, as a string
     * @param int|null $scale Optional scale parameter
     * @return string The sum of the two operands, as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcadd(string $num1, string $num2, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        $dec1 = self::getDecimal($num1);
        $dec2 = self::getDecimal($num2);
        $maxDec = max($dec1, $dec2, $scale);

        $int1 = self::toInt($num1, $maxDec);
        $int2 = self::toInt($num2, $maxDec);
        $result = self::addBigInt($int1, $int2);

        return self::formatResult($result, $maxDec, $scale);
    }

    /**
     * Compare two arbitrary precision numbers.
     *
     * @see https://www.php.net/manual/en/function.bccomp.php
     *
     * @param string $num1 The left operand, as a string
     * @param string $num2 The right operand, as a string
     * @param int|null $scale Optional scale parameter
     * @return int 0 if equal, 1 if num1 > num2, -1 if num1 < num2
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bccomp(string $num1, string $num2, ?int $scale = null): int
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        $neg1 = str_starts_with($num1, '-');
        $neg2 = str_starts_with($num2, '-');

        if ($neg1 && !$neg2) {
            return -1;
        }
        if (!$neg1 && $neg2) {
            return 1;
        }

        $abs1 = $neg1 ? substr($num1, 1) : $num1;
        $abs2 = $neg2 ? substr($num2, 1) : $num2;

        $dec1 = self::getDecimal($abs1);
        $dec2 = self::getDecimal($abs2);
        $maxDec = max($dec1, $dec2, $scale);

        $int1 = self::toInt($abs1, $maxDec);
        $int2 = self::toInt($abs2, $maxDec);

        $cmp = self::compareBigInt($int1, $int2);

        return $neg1 ? -$cmp : $cmp;
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @see https://www.php.net/manual/en/function.bcdiv.php
     *
     * @param string $num1 The dividend, as a string
     * @param string $num2 The divisor, as a string
     * @param int|null $scale Optional scale parameter
     * @return string The quotient, as a string, or null if divisor is 0
     * @throws ValueError If divisor is zero
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcdiv(string $num1, string $num2, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        if (self::bccomp($num2, '0', 0) === 0) {
            throw new ValueError('bcdiv(): Argument #2 ($num2) must not be zero');
        }

        $neg1 = str_starts_with($num1, '-');
        $neg2 = str_starts_with($num2, '-');
        $resultNeg = $neg1 !== $neg2;

        $abs1 = $neg1 ? substr($num1, 1) : $num1;
        $abs2 = $neg2 ? substr($num2, 1) : $num2;

        $dec1 = self::getDecimal($abs1);
        $dec2 = self::getDecimal($abs2);
        $maxDec = max($dec1, $dec2, $scale) + $scale + 1;

        $int1 = self::toInt($abs1, $maxDec);
        $int2 = self::toInt($abs2, $maxDec);

        if (self::compareBigInt($int1, '0') === 0) {
            return '0';
        }

        $result = self::divBigInt($int1, $int2);

        $padScale = $maxDec - $scale;
        $result = self::formatResult($result, $padScale, $scale);

        return ($resultNeg && $result !== '0' ? '-' : '') . $result;
    }

    /**
     * Get the quotient and modulus of an arbitrary precision number.
     *
     * @see https://www.php.net/manual/en/function.bcdivmod.php
     *
     * @param string $num1 The dividend, as a string
     * @param string $num2 The divisor, as a string
     * @param int|null $scale Optional scale parameter
     * @return array{0: string, 1: string} Array containing quotient and remainder
     * @throws ValueError If divisor is zero
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcdivmod(string $num1, string $num2, ?int $scale = null): array
    {
        $scale = $scale ?? self::getScale();

        $quotient = self::bcdiv($num1, $num2, $scale);
        $product = self::bcmul($quotient, $num2, $scale);
        $remainder = self::bcsub($num1, $product, $scale);

        return [$quotient, $remainder];
    }

    /**
     * Get modulus of an arbitrary precision number.
     *
     * @see https://www.php.net/manual/en/function.bcmod.php
     *
     * @param string $num1 The dividend, as a string
     * @param string $num2 The divisor, as a string
     * @param int|null $scale Optional scale parameter
     * @return string The modulus, as a string
     * @throws ValueError If divisor is zero
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcmod(string $num1, string $num2, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        if (self::bccomp($num2, '0', 0) === 0) {
            throw new ValueError('bcmod(): Argument #2 ($num2) must not be zero');
        }

        $neg1 = str_starts_with($num1, '-');
        $abs1 = $neg1 ? substr($num1, 1) : $num1;
        $abs2 = str_starts_with($num2, '-') ? substr($num2, 1) : $num2;

        $dec1 = self::getDecimal($abs1);
        $dec2 = self::getDecimal($abs2);
        $maxDec = max($dec1, $dec2, $scale);

        $int1 = self::toInt($abs1, $maxDec);
        $int2 = self::toInt($abs2, $maxDec);

        if (self::compareBigInt($int1, '0') === 0) {
            return self::formatResult('0', $maxDec, $scale);
        }

        $mod = self::modBigInt($int1, $int2);
        $result = self::formatResult($mod, $maxDec, $scale);

        return ($neg1 && $result !== '0' ? '-' : '') . $result;
    }

    /**
     * Multiply two arbitrary precision numbers.
     *
     * @see https://www.php.net/manual/en/function.bcmul.php
     *
     * @param string $num1 The left operand, as a string
     * @param string $num2 The right operand, as a string
     * @param int|null $scale Optional scale parameter
     * @return string The product, as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcmul(string $num1, string $num2, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        $neg1 = str_starts_with($num1, '-');
        $neg2 = str_starts_with($num2, '-');
        $resultNeg = $neg1 !== $neg2;

        $abs1 = $neg1 ? substr($num1, 1) : $num1;
        $abs2 = $neg2 ? substr($num2, 1) : $num2;

        $dec1 = self::getDecimal($abs1);
        $dec2 = self::getDecimal($abs2);
        $totalDec = $dec1 + $dec2;

        $int1 = self::toInt($abs1, $dec1);
        $int2 = self::toInt($abs2, $dec2);

        $result = self::mulBigInt($int1, $int2);

        $result = self::formatResult($result, $totalDec, $scale);

        return ($resultNeg && $result !== '0' ? '-' : '') . $result;
    }

    /**
     * Raise an arbitrary precision number to another.
     *
     * @see https://www.php.net/manual/en/function.bcpow.php
     *
     * @param string $num The base, as a string
     * @param string $exponent The exponent, as a string (must be integer)
     * @param int|null $scale Optional scale parameter
     * @return string The result, as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcpow(string $num, string $exponent, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num = self::normalize($num);
        $exponent = self::normalize($exponent);

        $expInt = (int) $exponent;

        if ($expInt === 0) {
            return '1';
        }

        if ($expInt < 0) {
            return self::bcdiv('1', self::bcpow($num, (string) abs($expInt), $scale + 2), $scale);
        }

        $result = '1';
        $base = $num;

        while ($expInt > 0) {
            if ($expInt % 2 === 1) {
                $result = self::bcmul($result, $base, $scale + 2);
            }
            $base = self::bcmul($base, $base, $scale + 2);
            $expInt = intdiv($expInt, 2);
        }

        return self::formatResult(self::toInt($result, 0), 0, $scale);
    }

    /**
     * Raise an arbitrary precision number to another, reduced by a specified modulus.
     *
     * @see https://www.php.net/manual/en/function.bcpowmod.php
     *
     * @param string $num The base, as a string (must be non-negative integer)
     * @param string $exponent The exponent, as a string (must be non-negative integer)
     * @param string $modulus The modulus, as a string (must be non-zero integer)
     * @param int|null $scale Optional scale parameter
     * @return string The result, as a string, or false on error
     * @throws ValueError If modulus is zero or arguments are not integers
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcpowmod(string $num, string $exponent, string $modulus, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num = self::normalize($num);
        $exponent = self::normalize($exponent);
        $modulus = self::normalize($modulus);

        if (self::bccomp($modulus, '0', 0) === 0) {
            throw new ValueError('bcpowmod(): Argument #3 ($modulus) must not be zero');
        }

        if (self::bccomp($num, '0', 0) < 0 || self::bccomp($exponent, '0', 0) < 0) {
            throw new ValueError('bcpowmod(): Arguments must be non-negative integers');
        }

        $expInt = (int) $exponent;

        if ($expInt === 0) {
            return '1';
        }

        $result = '1';
        $base = self::bcmod($num, $modulus, 0);

        while ($expInt > 0) {
            if ($expInt % 2 === 1) {
                $result = self::bcmod(self::bcmul($result, $base, 0), $modulus, 0);
            }
            $base = self::bcmod(self::bcmul($base, $base, 0), $modulus, 0);
            $expInt = intdiv($expInt, 2);
        }

        return $result;
    }

    /**
     * Set or get default scale parameter for all bc math functions.
     *
     * @see https://www.php.net/manual/en/function.bcscale.php
     *
     * @param int|null $scale The scale value to set, or null to get current value
     * @return int The current scale value
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcscale(?int $scale = null): int
    {
        if ($scale !== null) {
            self::$scale = $scale;
        }

        return self::$scale ?? 0;
    }

    /**
     * Get the square root of an arbitrary precision number.
     *
     * @see https://www.php.net/manual/en/function.bcsqrt.php
     *
     * @param string $num The operand, as a string (must be non-negative)
     * @param int|null $scale Optional scale parameter
     * @return string The square root, as a string
     * @throws ValueError If number is negative
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcsqrt(string $num, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num = self::normalize($num);

        if (self::bccomp($num, '0', 0) < 0) {
            throw new ValueError('bcsqrt(): Argument #1 ($num) must be non-negative');
        }

        if (self::bccomp($num, '0', 0) === 0) {
            return '0';
        }

        if (self::bccomp($num, '1', 0) === 0) {
            return self::formatResult('1', 0, $scale);
        }

        $dec = self::getDecimal($num);
        $int = self::toInt($num, $dec + $scale + 2);

        $low = '0';
        $high = $int;

        while (self::compareBigInt($low, $high) < 0) {
            $mid = self::addBigInt($low, self::subBigInt($high, $low));
            $mid = self::divBigInt($mid, '2');
            $sq = self::mulBigInt($mid, $mid);

            $cmp = self::compareBigInt($sq, $int);
            if ($cmp === 0) {
                return self::formatResult($mid, $dec + $scale + 2, $scale);
            } elseif ($cmp < 0) {
                $low = self::addBigInt($mid, '1');
            } else {
                $high = $mid;
            }
        }

        return self::formatResult(self::subBigInt($low, '1'), $dec + $scale + 2, $scale);
    }

    /**
     * Subtract one arbitrary precision number from another.
     *
     * @see https://www.php.net/manual/en/function.bcsub.php
     *
     * @param string $num1 The left operand, as a string
     * @param string $num2 The right operand, as a string
     * @param int|null $scale Optional scale parameter
     * @return string The difference, as a string
     */
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function bcsub(string $num1, string $num2, ?int $scale = null): string
    {
        $scale = $scale ?? self::getScale();
        $num1 = self::normalize($num1);
        $num2 = self::normalize($num2);

        $dec1 = self::getDecimal($num1);
        $dec2 = self::getDecimal($num2);
        $maxDec = max($dec1, $dec2, $scale);

        $int1 = self::toInt($num1, $maxDec);
        $int2 = self::toInt($num2, $maxDec);
        $result = self::subBigInt($int1, $int2);

        return self::formatResult($result, $maxDec, $scale);
    }

    private static function addAbsBigInt(string $a, string $b): string
    {
        $a = ltrim($a, '0');
        if ($a === '') {
            $a = '0';
        }
        $b = ltrim($b, '0');
        if ($b === '') {
            $b = '0';
        }

        $result = '';
        $carry = 0;
        $lenA = strlen($a);
        $lenB = strlen($b);
        $maxLen = max($lenA, $lenB);

        // phpcs:ignore
        for ($i = 0; $i < $maxLen; ++$i) {
            $digitA = $i < $lenA ? (int) $a[$lenA - 1 - $i] : 0;
            $digitB = $i < $lenB ? (int) $b[$lenB - 1 - $i] : 0;
            $sum = $digitA + $digitB + $carry;
            $result = ($sum % 10) . $result;
            $carry = intdiv($sum, 10);
        }

        if ($carry > 0) {
            $result = $carry . $result;
        }

        return $result;
    }

    private static function addBigInt(string $a, string $b): string
    {
        $negA = str_starts_with($a, '-');
        $negB = str_starts_with($b, '-');

        $absA = $negA ? substr($a, 1) : $a;
        $absB = $negB ? substr($b, 1) : $b;

        $absA = ltrim($absA, '0');
        if ($absA === '') {
            $absA = '0';
        }
        $absB = ltrim($absB, '0');
        if ($absB === '') {
            $absB = '0';
        }

        if (!$negA && !$negB) {
            return self::addAbsBigInt($absA, $absB);
        }
        if ($negA && $negB) {
            return '-' . self::addAbsBigInt($absA, $absB);
        }

        $cmp = self::compareBigInt($absA, $absB);
        if ($cmp === 0) {
            return '0';
        }

        if ($cmp > 0) {
            $result = self::subAbsBigInt($absA, $absB);

            return ($negA ? '-' : '') . $result;
        }

        $result = self::subAbsBigInt($absB, $absA);

        return ($negB ? '-' : '') . $result;
    }

    private static function compareBigInt(string $a, string $b): int
    {
        $negA = str_starts_with($a, '-');
        $negB = str_starts_with($b, '-');

        if ($negA && !$negB) {
            return -1;
        }
        if (!$negA && $negB) {
            return 1;
        }

        $absA = $negA ? substr($a, 1) : $a;
        $absB = $negB ? substr($b, 1) : $b;

        $absA = ltrim($absA, '0');
        if ($absA === '') {
            $absA = '0';
        }
        $absB = ltrim($absB, '0');
        if ($absB === '') {
            $absB = '0';
        }

        if (strlen($absA) !== strlen($absB)) {
            $result = strlen($absA) > strlen($absB) ? 1 : -1;

            return $negA ? -$result : $result;
        }

        if ($absA === $absB) {
            return 0;
        }

        $result = $absA > $absB ? 1 : -1;

        return $negA ? -$result : $result;
    }

    private static function divBigInt(string $a, string $b): string
    {
        $a = ltrim($a, '0');
        if ($a === '') {
            $a = '0';
        }
        $b = ltrim($b, '0');
        if ($b === '') {
            $b = '0';
        }

        if ($b === '0') {
            throw new ValueError('Division by zero');
        }

        if (self::compareBigInt($a, $b) < 0) {
            return '0';
        }

        $result = '0';
        $current = '0';

        // phpcs:ignore
        for ($i = 0; $i < strlen($a); ++$i) {
            $current = ltrim($current, '0');
            if ($current === '') {
                $current = '0';
            }
            $current .= $a[$i];

            $count = 0;
            while (self::compareBigInt($current, $b) >= 0) {
                $current = self::subAbsBigInt($current, $b);
                // phpcs:ignore
                ++$count;
            }

            $result .= $count;
        }

        $result = ltrim($result, '0');
        if ($result === '') {
            $result = '0';
        }

        return $result;
    }

    private static function formatResult(string $num, int $fromDecimals, int $toDecimals): string
    {
        $neg = str_starts_with($num, '-');
        $abs = $neg ? substr($num, 1) : $num;
        $abs = ltrim($abs, '0');
        if ($abs === '') {
            $abs = '0';
        }

        if ($fromDecimals > 0) {
            while (strlen($abs) <= $fromDecimals) {
                $abs = '0' . $abs;
            }
            $intPart = substr($abs, 0, -$fromDecimals);
            if ($intPart === '') {
                $intPart = '0';
            }
            $decPart = substr($abs, -$fromDecimals);

            if ($toDecimals > 0) {
                while (strlen($decPart) < $toDecimals) {
                    $decPart .= '0';
                }
                if (strlen($decPart) > $toDecimals) {
                    $decPart = substr($decPart, 0, $toDecimals);
                }
                $abs = $intPart . '.' . $decPart;
            } else {
                $abs = $intPart;
            }
        } else {
            if ($toDecimals > 0) {
                $abs .= '.' . str_repeat('0', $toDecimals);
            }
        }

        $abs = self::trimTrailingZeros($abs);
        if ($abs === '') {
            $abs = '0';
        }

        return ($neg && $abs !== '0' ? '-' : '') . $abs;
    }

    private static function getDecimal(string $num): int
    {
        $pos = strpos($num, '.');

        return $pos === false ? 0 : strlen($num) - $pos - 1;
    }

    private static function getScale(): int
    {
        return self::$scale ?? 0;
    }

    private static function modBigInt(string $a, string $b): string
    {
        $a = ltrim($a, '0');
        if ($a === '') {
            $a = '0';
        }
        $b = ltrim($b, '0');
        if ($b === '') {
            $b = '0';
        }

        if ($b === '0') {
            throw new ValueError('Division by zero');
        }

        $current = '0';

        // phpcs:ignore
        for ($i = 0; $i < strlen($a); ++$i) {
            $current = ltrim($current, '0');
            if ($current === '') {
                $current = '0';
            }
            $current .= $a[$i];

            while (self::compareBigInt($current, $b) >= 0) {
                $current = self::subAbsBigInt($current, $b);
            }
        }

        return $current;
    }

    private static function mulBigInt(string $a, string $b): string
    {
        $a = ltrim($a, '0');
        if ($a === '') {
            $a = '0';
        }
        $b = ltrim($b, '0');
        if ($b === '') {
            $b = '0';
        }

        if ($a === '0' || $b === '0') {
            return '0';
        }

        $lenA = strlen($a);
        $lenB = strlen($b);
        $result = array_fill(0, $lenA + $lenB, 0);

        for ($i = $lenA - 1; $i >= 0; $i--) {
            for ($j = $lenB - 1; $j >= 0; $j--) {
                $product = (int) $a[$i] * (int) $b[$j];
                $pos1 = $i + $j;
                $pos2 = $i + $j + 1;
                $sum = $product + $result[$pos2];

                $result[$pos2] = $sum % 10;
                $result[$pos1] += intdiv($sum, 10);
            }
        }

        $resultStr = implode('', $result);
        $resultStr = ltrim($resultStr, '0');
        if ($resultStr === '') {
            $resultStr = '0';
        }

        return $resultStr;
    }

    private static function normalize(string $num): string
    {
        $num = trim($num);

        if ($num === '' || $num === '-') {
            return '0';
        }

        if (!preg_match('/^-?\d*\.?\d*$/', $num)) {
            return '0';
        }

        if (str_contains($num, '.')) {
            $num = rtrim(rtrim($num, '0'), '.');
        }

        $neg = str_starts_with($num, '-');
        $abs = $neg ? substr($num, 1) : $num;
        $abs = ltrim($abs, '0');
        if ($abs === '') {
            $abs = '0';
        }

        $num = ($neg && $abs !== '0' ? '-' : '') . $abs;

        return $num === '-0' ? '0' : $num;
    }

    private static function subAbsBigInt(string $a, string $b): string
    {
        $a = ltrim($a, '0');
        if ($a === '') {
            $a = '0';
        }
        $b = ltrim($b, '0');
        if ($b === '') {
            $b = '0';
        }

        $result = '';
        $borrow = 0;
        $lenA = strlen($a);
        $lenB = strlen($b);

        // phpcs:ignore
        for ($i = 0; $i < $lenA; ++$i) {
            $digitA = (int) $a[$lenA - 1 - $i];
            $digitB = $i < $lenB ? (int) $b[$lenB - 1 - $i] : 0;
            $diff = $digitA - $digitB - $borrow;

            if ($diff < 0) {
                $diff += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }

            $result = $diff . $result;
        }

        $result = ltrim($result, '0');
        if ($result === '') {
            $result = '0';
        }

        return $result;
    }

    private static function subBigInt(string $a, string $b): string
    {
        $negB = str_starts_with($b, '-');
        if ($negB) {
            return self::addBigInt($a, substr($b, 1));
        }

        return self::addBigInt($a, '-' . $b);
    }

    private static function toInt(string $num, int $decimals): string
    {
        $num = str_replace('.', '', $num);
        $currentDec = 0;

        if (($pos = strpos($num, '.')) !== false) {
            $currentDec = strlen($num) - $pos - 1;
        }

        $num = str_replace('.', '', $num);
        $neg = str_starts_with($num, '-');
        $abs = $neg ? substr($num, 1) : $num;

        $pad = $decimals - $currentDec;
        if ($pad > 0) {
            $abs .= str_repeat('0', $pad);
        } elseif ($pad < 0) {
            $abs = substr($abs, 0, $pad);
        }

        $abs = ltrim($abs, '0');
        if ($abs === '') {
            $abs = '0';
        }

        return ($neg && $abs !== '0' ? '-' : '') . $abs;
    }

    private static function trimTrailingZeros(string $num): string
    {
        if (!str_contains($num, '.')) {
            return $num;
        }

        $num = rtrim(rtrim($num, '0'), '.');
        if ($num === '' || $num === '-') {
            return '0';
        }

        return $num;
    }
}
