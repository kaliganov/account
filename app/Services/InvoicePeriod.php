<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class InvoicePeriod
{
    /** Скользящее окно: N месяцев назад и вперёд от текущего. */
    public const WINDOW_MONTHS = 12;

    public static function parse(string $yearMonth): CarbonImmutable
    {
        $dt = CarbonImmutable::createFromFormat('!Y-m', $yearMonth);
        if ($dt === false || $dt->format('Y-m') !== $yearMonth) {
            throw new InvalidArgumentException('Некорректный период: '.$yearMonth);
        }

        return $dt;
    }

    public static function invoiceDate(string $yearMonth): CarbonImmutable
    {
        return self::parse($yearMonth)->endOfMonth()->startOfDay();
    }

    public static function issueDate(?CarbonImmutable $now = null): CarbonImmutable
    {
        return ($now ?? CarbonImmutable::now())->startOfDay();
    }

    public static function servicesText(string $yearMonth): string
    {
        $dt = self::parse($yearMonth);
        $monthsPrep = [
            1 => 'январе',
            2 => 'феврале',
            3 => 'марте',
            4 => 'апреле',
            5 => 'мае',
            6 => 'июне',
            7 => 'июле',
            8 => 'августе',
            9 => 'сентябре',
            10 => 'октябре',
            11 => 'ноябре',
            12 => 'декабре',
        ];

        $monthNum = (int) $dt->format('n');
        $m = $monthsPrep[$monthNum];

        return "Бухгалтерское сопровождение в {$m} {$dt->format('Y')} г.";
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(?CarbonImmutable $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now())->startOfMonth();
        $items = [];

        for ($i = -self::WINDOW_MONTHS; $i <= self::WINDOW_MONTHS; $i++) {
            $dt = $now->addMonthsNoOverflow($i);
            $items[] = [
                'value' => $dt->format('Y-m'),
                'label' => mb_convert_case(
                    $dt->locale('ru')->translatedFormat('F Y'),
                    MB_CASE_TITLE,
                    'UTF-8'
                ),
            ];
        }

        return array_reverse($items);
    }

    /**
     * @return list<string>
     */
    public static function allowedValues(?CarbonImmutable $now = null): array
    {
        return array_column(self::options($now), 'value');
    }

    public static function isAllowed(string $yearMonth, ?CarbonImmutable $now = null): bool
    {
        return in_array($yearMonth, self::allowedValues($now), true);
    }
}
