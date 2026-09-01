<?php

namespace FluxErp\Traits\Livewire\Widget;

use InvalidArgumentException;

trait HasTrendExpressions
{
    protected function getTrendExpression(string $driver, string $dateColumn, string $unit): string
    {
        return match ($driver) {
            'sqlite' => match ($unit) {
                'year' => "STRFTIME('%Y', $dateColumn)",
                'month' => "STRFTIME('%Y-%m', $dateColumn)",
                'week' => "STRFTIME('%Y-W', $dateColumn) ||
                        PRINTF('%02d', STRFTIME('%W', $dateColumn) + (1 - STRFTIME('%W', STRFTIME('%Y', $dateColumn) || '-01-04')) )",
                'day' => "STRFTIME('%Y-%m-%d', $dateColumn)",
                'hour' => "STRFTIME('%Y-%m-%d %H:00', $dateColumn)",
                'minute' => "STRFTIME('%Y-%m-%d %H:%M:00', $dateColumn)",
            },
            'mysql' => match ($unit) {
                'year' => "DATE_FORMAT($dateColumn, '%Y')",
                'month' => "DATE_FORMAT($dateColumn, '%Y-%m')",
                'week' => "CONCAT(DATE_FORMAT($dateColumn, '%x'), '-W', LPAD(DATE_FORMAT($dateColumn, '%v'), 2, '0'))",
                'day' => "DATE_FORMAT($dateColumn, '%Y-%m-%d')",
                'hour' => "DATE_FORMAT($dateColumn, '%Y-%m-%d %H:00')",
                'minute' => "DATE_FORMAT($dateColumn, '%Y-%m-%d %H:%i:00')",
            },
            'pgsql' => match ($unit) {
                'year' => "TO_CHAR($dateColumn, 'YYYY')",
                'month' => "TO_CHAR($dateColumn, 'YYYY-MM')",
                'week' => "TO_CHAR($dateColumn, 'IYYY') || '-W' || TO_CHAR($dateColumn, 'IW')",
                'day' => "TO_CHAR($dateColumn, 'YYYY-MM-DD')",
                'hour' => "TO_CHAR($dateColumn, 'YYYY-MM-DD HH24:00')",
                'minute' => "TO_CHAR($dateColumn, 'YYYY-MM-DD HH24:mi:00')",
            },
            'sqlsrv' => match ($unit) {
                'year' => "FORMAT($dateColumn, 'yyyy')",
                'month' => "FORMAT($dateColumn, 'yyyy-MM')",
                'week' => "CONCAT(YEAR($dateColumn), '-W', RIGHT('0' + CAST(DATEPART(ISO_WEEK, $dateColumn) AS VARCHAR(2)), 2))",
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
