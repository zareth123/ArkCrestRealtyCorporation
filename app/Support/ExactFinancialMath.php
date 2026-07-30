<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Exact base-10 financial calculations without FLOAT/DOUBLE.
 *
 * Percentages are preserved as decimal strings with up to 30 decimal places.
 * Monetary results are rounded only once, to centavos, when a peso amount must
 * be stored. Percentage calculations are truncated (never rounded) at MySQL's
 * maximum DECIMAL scale of 30.
 */
final class ExactFinancialMath
{
    public const PERCENT_SCALE = 30;

    public static function normalizePercentage(mixed $value, int $maxScale = self::PERCENT_SCALE): string
    {
        [$digits, $scale] = self::decimalDigits($value, $maxScale, false);

        return self::formatScaled($digits, $scale, true);
    }

    public static function normalizeMoney(mixed $value): string
    {
        [$digits] = self::decimalDigits($value, 2, true);

        return self::formatScaled($digits, 2, false);
    }

    public static function formatMoney(mixed $value): string
    {
        $money = self::normalizeMoney($value);
        [$whole, $fraction] = explode('.', $money, 2);
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);

        return $whole . '.' . $fraction;
    }

    public static function multiplyToMoney(mixed $left, mixed $right): string
    {
        [$leftDigits, $leftScale] = self::decimalDigits($left, null, false);
        [$rightDigits, $rightScale] = self::decimalDigits($right, null, false);

        $product = self::multiplyIntegers($leftDigits, $rightDigits);
        $productScale = $leftScale + $rightScale;
        $cents = self::rescaleInteger($product, $productScale, 2, true);

        return self::formatScaled($cents, 2, false);
    }

    public static function moneyFromPercentage(mixed $baseAmount, mixed $percentage): string
    {
        [$baseCents] = self::decimalDigits($baseAmount, 2, true);
        [$percentDigits, $percentScale] = self::decimalDigits($percentage, self::PERCENT_SCALE, false);

        if ($baseCents === '0' || $percentDigits === '0') {
            return '0.00';
        }

        $numerator = self::multiplyIntegers($baseCents, $percentDigits);
        $denominator = '1' . str_repeat('0', $percentScale + 2); // 100 × 10^percentage scale
        $cents = self::divideRounded($numerator, $denominator);

        return self::formatScaled($cents, 2, false);
    }

    public static function percentageFromAmount(
        mixed $amount,
        mixed $baseAmount,
        int $scale = self::PERCENT_SCALE
    ): string {
        [$amountCents] = self::decimalDigits($amount, 2, true);
        [$baseCents] = self::decimalDigits($baseAmount, 2, true);

        if ($amountCents === '0' || $baseCents === '0') {
            return '0';
        }

        $numerator = self::multiplyBySmall($amountCents, 100);
        $scaledNumerator = $numerator . str_repeat('0', $scale);
        [$scaledPercent] = self::divideIntegers($scaledNumerator, $baseCents);

        // Deliberately truncate at 30 decimal places. Never inspect the next
        // digit and never increment the stored percentage.
        return self::formatScaled($scaledPercent, $scale, true);
    }

    public static function subtractMoney(mixed $left, mixed $right): string
    {
        [$leftCents] = self::decimalDigits($left, 2, true);
        [$rightCents] = self::decimalDigits($right, 2, true);

        $result = self::compareIntegers($leftCents, $rightCents) >= 0
            ? self::subtractIntegers($leftCents, $rightCents)
            : '0';

        return self::formatScaled($result, 2, false);
    }

    public static function clampMoney(mixed $value, mixed $minimum, mixed $maximum): string
    {
        [$valueCents] = self::decimalDigits($value, 2, true);
        [$minimumCents] = self::decimalDigits($minimum, 2, true);
        [$maximumCents] = self::decimalDigits($maximum, 2, true);

        if (self::compareIntegers($valueCents, $minimumCents) < 0) {
            $valueCents = $minimumCents;
        }
        if (self::compareIntegers($valueCents, $maximumCents) > 0) {
            $valueCents = $maximumCents;
        }

        return self::formatScaled($valueCents, 2, false);
    }

    public static function divideMoney(mixed $amount, int $divisor): string
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException('Divisor must be greater than zero.');
        }

        [$cents] = self::decimalDigits($amount, 2, true);
        $result = self::divideRounded($cents, (string) $divisor);

        return self::formatScaled($result, 2, false);
    }

    private static function decimalDigits(mixed $value, ?int $fixedScale, bool $round): array
    {
        $normalized = str_replace([',', ' '], '', trim((string) ($value ?? '0')));
        if ($normalized === '') {
            $normalized = '0';
        }

        if (!preg_match('/^\+?(\d*)(?:\.(\d*))?$/', $normalized, $matches)) {
            throw new InvalidArgumentException('Invalid decimal value: ' . $normalized);
        }

        $whole = ltrim($matches[1] !== '' ? $matches[1] : '0', '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = $matches[2] ?? '';

        if ($fixedScale === null) {
            $fraction = rtrim($fraction, '0');
            $scale = strlen($fraction);
        } else {
            $scale = $fixedScale;
            $nextDigit = strlen($fraction) > $scale ? (int) $fraction[$scale] : 0;
            $fraction = substr(str_pad($fraction, $scale, '0'), 0, $scale);
        }

        $digits = self::normalizeInteger($whole . $fraction);

        if ($fixedScale !== null && $round && $nextDigit >= 5) {
            $digits = self::addIntegers($digits, '1');
        }

        return [$digits, $scale];
    }

    private static function rescaleInteger(string $digits, int $fromScale, int $toScale, bool $round): string
    {
        if ($fromScale === $toScale) {
            return self::normalizeInteger($digits);
        }

        if ($fromScale < $toScale) {
            return self::normalizeInteger($digits . str_repeat('0', $toScale - $fromScale));
        }

        $difference = $fromScale - $toScale;
        $denominator = '1' . str_repeat('0', $difference);

        return $round
            ? self::divideRounded($digits, $denominator)
            : self::divideIntegers($digits, $denominator)[0];
    }

    private static function formatScaled(string $digits, int $scale, bool $trimTrailingZeros): string
    {
        $digits = self::normalizeInteger($digits);

        if ($scale === 0) {
            return $digits;
        }

        $digits = str_pad($digits, $scale + 1, '0', STR_PAD_LEFT);
        $whole = substr($digits, 0, -$scale);
        $fraction = substr($digits, -$scale);

        if ($trimTrailingZeros) {
            $fraction = rtrim($fraction, '0');
        }

        return $fraction === '' ? $whole : $whole . '.' . $fraction;
    }

    private static function normalizeInteger(string $value): string
    {
        $value = ltrim($value, '0');

        return $value === '' ? '0' : $value;
    }

    private static function compareIntegers(string $left, string $right): int
    {
        $left = self::normalizeInteger($left);
        $right = self::normalizeInteger($right);

        if (strlen($left) !== strlen($right)) {
            return strlen($left) <=> strlen($right);
        }

        return strcmp($left, $right) <=> 0;
    }

    private static function addIntegers(string $left, string $right): string
    {
        $leftIndex = strlen($left) - 1;
        $rightIndex = strlen($right) - 1;
        $carry = 0;
        $result = '';

        while ($leftIndex >= 0 || $rightIndex >= 0 || $carry > 0) {
            $sum = $carry;
            if ($leftIndex >= 0) {
                $sum += (int) $left[$leftIndex--];
            }
            if ($rightIndex >= 0) {
                $sum += (int) $right[$rightIndex--];
            }
            $result = ($sum % 10) . $result;
            $carry = intdiv($sum, 10);
        }

        return self::normalizeInteger($result);
    }

    private static function subtractIntegers(string $left, string $right): string
    {
        if (self::compareIntegers($left, $right) < 0) {
            throw new InvalidArgumentException('Unsigned integer subtraction would be negative.');
        }

        $leftIndex = strlen($left) - 1;
        $rightIndex = strlen($right) - 1;
        $borrow = 0;
        $result = '';

        while ($leftIndex >= 0) {
            $digit = (int) $left[$leftIndex] - $borrow;
            $subtract = $rightIndex >= 0 ? (int) $right[$rightIndex] : 0;

            if ($digit < $subtract) {
                $digit += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }

            $result = ($digit - $subtract) . $result;
            $leftIndex--;
            $rightIndex--;
        }

        return self::normalizeInteger($result);
    }

    private static function multiplyBySmall(string $value, int $multiplier): string
    {
        if ($multiplier < 0 || $multiplier > 1000) {
            throw new InvalidArgumentException('Small multiplier must be between 0 and 1000.');
        }

        if ($value === '0' || $multiplier === 0) {
            return '0';
        }

        $carry = 0;
        $result = '';

        for ($index = strlen($value) - 1; $index >= 0; $index--) {
            $product = ((int) $value[$index] * $multiplier) + $carry;
            $result = ($product % 10) . $result;
            $carry = intdiv($product, 10);
        }

        while ($carry > 0) {
            $result = ($carry % 10) . $result;
            $carry = intdiv($carry, 10);
        }

        return self::normalizeInteger($result);
    }

    private static function multiplyIntegers(string $left, string $right): string
    {
        $left = self::normalizeInteger($left);
        $right = self::normalizeInteger($right);

        if ($left === '0' || $right === '0') {
            return '0';
        }

        $result = array_fill(0, strlen($left) + strlen($right), 0);

        for ($leftIndex = strlen($left) - 1; $leftIndex >= 0; $leftIndex--) {
            for ($rightIndex = strlen($right) - 1; $rightIndex >= 0; $rightIndex--) {
                $position = $leftIndex + $rightIndex + 1;
                $sum = $result[$position] + ((int) $left[$leftIndex] * (int) $right[$rightIndex]);
                $result[$position] = $sum % 10;
                $result[$position - 1] += intdiv($sum, 10);
            }
        }

        return self::normalizeInteger(implode('', $result));
    }

    private static function divideRounded(string $numerator, string $denominator): string
    {
        [$quotient, $remainder] = self::divideIntegers($numerator, $denominator);
        $doubleRemainder = self::multiplyBySmall($remainder, 2);

        return self::compareIntegers($doubleRemainder, $denominator) >= 0
            ? self::addIntegers($quotient, '1')
            : $quotient;
    }

    private static function divideIntegers(string $numerator, string $denominator): array
    {
        $numerator = self::normalizeInteger($numerator);
        $denominator = self::normalizeInteger($denominator);

        if ($denominator === '0') {
            throw new InvalidArgumentException('Division by zero.');
        }

        if (self::compareIntegers($numerator, $denominator) < 0) {
            return ['0', $numerator];
        }

        $quotient = '';
        $remainder = '0';

        for ($index = 0, $length = strlen($numerator); $index < $length; $index++) {
            $remainder = self::normalizeInteger($remainder . $numerator[$index]);
            $digit = 0;

            while (self::compareIntegers($remainder, $denominator) >= 0) {
                $remainder = self::subtractIntegers($remainder, $denominator);
                $digit++;
            }

            $quotient .= (string) $digit;
        }

        return [self::normalizeInteger($quotient), self::normalizeInteger($remainder)];
    }
}
