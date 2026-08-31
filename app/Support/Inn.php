<?php

namespace App\Support;

final class Inn
{
    public static function isValid(string $inn): bool
    {
        $inn = trim($inn);

        if (preg_match('/^\d{10}$/', $inn) === 1) {
            return self::controlDigit($inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]) === (int) $inn[9];
        }

        if (preg_match('/^\d{12}$/', $inn) === 1) {
            $n11 = self::controlDigit($inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
            $n12 = self::controlDigit($inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);

            return $n11 === (int) $inn[10] && $n12 === (int) $inn[11];
        }

        return false;
    }

    /**
     * @param  list<int>  $coefficients
     */
    private static function controlDigit(string $inn, array $coefficients): int
    {
        $sum = 0;
        foreach ($coefficients as $i => $k) {
            $sum += (int) $inn[$i] * $k;
        }

        return $sum % 11 % 10;
    }
}
