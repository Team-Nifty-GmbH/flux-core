<?php

namespace FluxErp\Traits\Livewire\Widget;

use InvalidArgumentException;

trait HasTrendExpressions
{
    protected function getTrendExpression(string $driver, string $dateColumn, string $unit): string
    {
        return match ($driver) {
            'sqlite' => match ($unit) {
                'year' => "strftime('%Y', $dateColumn)",
                'month' => "strftime('%Y-%m', $dateColumn)",
                'week' => "strftime('%Y-W', $dateColumn) ||
                        printf('%02d', strftime('%W', $dateColumn) + (1 - strftime('%W', strftime('%Y', $dateColumn) || '-01-04')) )",
                'day' => "strftime('%Y-%m-%d', $dateColumn)",
                'hour' => "strftime('%Y-%m-%d %H:00', $dateColumn)",
                'minute' => "strftime('%Y-%m-%d %H:%M:00', $dateColumn)",
            },
            'mysql' => match ($unit) {
                'year' => "date_format($dateColumn, '%Y')",
                'month' => "date_format($dateColumn, '%Y-%m')",
                'week' => "CONCAT(date_format($dateColumn, '%x'), '-W', LPAD(date_format($dateColumn, '%v'), 2, '0'))",
                'day' => "date_format($dateColumn, '%Y-%m-%d')",
                'hour' => "date_format($dateColumn, '%Y-%m-%d %H:00')",
                'minute' => "date_format($dateColumn, '%Y-%m-%d %H:%i:00')",
            },
            'pgsql' => match ($unit) {
                'year' => "to_char($dateColumn, 'YYYY')",
                'month' => "to_char($dateColumn, 'YYYY-MM')",
                'week' => "to_char($dateColumn, 'IYYY') || '-W' || to_char($dateColumn, 'IW')",
                'day' => "to_char($dateColumn, 'YYYY-MM-DD')",
                'hour' => "to_char($dateColumn, 'YYYY-MM-DD HH24:00')",
                'minute' => "to_char($dateColumn, 'YYYY-MM-DD HH24:mi:00')",
            },
            'sqlsrv' => match ($unit) {
                'year' => "FORMAT($dateColumn, 'yyyy')",
                'month' => "FORMAT($dateColumn, 'yyyy-MM')",
                'week' => "concat(YEAR($dateColumn), '-W', RIGHT('0' + CAST(datepart(ISO_WEEK, $dateColumn) AS VARCHAR(2)), 2))",
                'day' => "FORMAT($dateColumn, 'yyyy-MM-dd')",
                'hour' => "FORMAT($dateColumn, 'yyyy-MM-dd HH:00')",
                'minute' => "FORMAT($dateColumn, 'yyyy-MM-dd HH:mm:00')",
            },
            default => throw new InvalidArgumentException('Metrics is not supported for this database.')
        };
    }

    protected function getTrendFormat(string $unit): string
    {
        return match ($unit) {
            'year' => 'Y',
            'month' => 'Y-m',
            'week' => 'o-\WW',
            'day' => 'Y-m-d',
            'hour' => 'Y-m-d H:00',
            'minute' => 'Y-m-d H:i:00',
            default => throw new InvalidArgumentException('Invalid unit: ' . $unit),
        };
    }
}
