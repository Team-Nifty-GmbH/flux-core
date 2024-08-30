<?php

namespace FluxErp\Support\Metrics;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Support\Metrics\Results\Result;
use InvalidArgumentException;

class Trend extends Metric
{
    protected string $unit;

    public function min(string $column, ?string $unit = null): Result
    {
        return $this->setType('min', $column, $unit);
    }

    public function max(string $column, ?string $unit = null): Result
    {
        return $this->setType('max', $column, $unit);
    }

    public function sum(string $column, ?string $unit = null): Result
    {
        return $this->setType('sum', $column, $unit);
    }

    public function avg(string $column, ?string $unit = null): Result
    {
        return $this->setType('avg', $column, $unit);
    }

    public function count(string $column = '*', ?string $unit = null): Result
    {
        return $this->setType('count', $column, $unit);
    }

    protected function setType(string $type, string $column, ?string $unit = null): Result
    {
        if (is_null($unit) && ! ($unit = $this->getRange()->getUnit())) {
            $diff = $this->startingDate?->diffInDays($this->endingDate ?? now()->addCenturies(2));

            $unit = match (true) {
                $diff < 30 => 'day',
                $diff < 365 => 'month',
                default => 'year',
            };
        }

        $this->type = $type;
        $this->unit = $unit;
        $this->column = $column;

        return $this->resolve();
    }

    protected function getExpression(): string
    {
        $grammar = $this->query->getQuery()->getGrammar();
        $driver = $this->query->getConnection()->getDriverName();
        $dateColumn = $grammar->wrap($this->getDateColumn());

        if (static::hasMacro($driver)) {
            return static::$driver($dateColumn, $this->unit);
        }

        return match ($driver) {
            'sqlite' => match ($this->unit) {
                'year' => "strftime('%Y', $dateColumn)",
                'month' => "strftime('%Y-%m', $dateColumn)",
                'week' => "strftime('%Y-', $dateColumn) ||
                        printf('%02d', strftime('%W', $dateColumn) + (1 - strftime('%W', strftime('%Y', $dateColumn) || '-01-04')) )",
                'day' => "strftime('%Y-%m-%d', $dateColumn)",
                'hour' => "strftime('%Y-%m-%d %H:00', $dateColumn)",
                'minute' => "strftime('%Y-%m-%d %H:%M:00', $dateColumn)",
            },
            'mysql' => match ($this->unit) {
                'year' => "date_format($dateColumn, '%Y')",
                'month' => "date_format($dateColumn, '%Y-%m')",
                'week' => "date_format($dateColumn, '%x-%v')",
                'day' => "date_format($dateColumn, '%Y-%m-%d')",
                'hour' => "date_format($dateColumn, '%Y-%m-%d %H:00')",
                'minute' => "date_format($dateColumn, '%Y-%m-%d %H:%i:00')",
            },
            'pgsql' => match ($this->unit) {
                'year' => "to_char($dateColumn, 'YYYY')",
                'month' => "to_char($dateColumn, 'YYYY-MM')",
                'week' => "to_char($dateColumn, 'IYYY-IW')",
                'day' => "to_char($dateColumn, 'YYYY-MM-DD')",
                'hour' => "to_char($dateColumn, 'YYYY-MM-DD HH24:00')",
                'minute' => "to_char($dateColumn, 'YYYY-MM-DD HH24:mi:00')",
            },
            'sqlsrv' => match ($this->unit) {
                'year' => "FORMAT($dateColumn, 'yyyy')",
                'month' => "FORMAT($dateColumn, 'yyyy-MM')",
                'week' => "concat(YEAR($dateColumn), '-', datepart(ISO_WEEK, $dateColumn))",
                'day' => "FORMAT($dateColumn, 'yyyy-MM-dd')",
                'hour' => "FORMAT($dateColumn, 'yyyy-MM-dd HH:00')",
                'minute' => "FORMAT($dateColumn, 'yyyy-MM-dd HH:mm:00')",
            },
            default => throw new InvalidArgumentException('Metrics is not supported for this database.')
        };
    }

    protected function getFormat(): string
    {
        return match ($this->unit) {
            'year' => 'Y',
            'month' => 'Y-m',
            'week' => 'Y-W',
            'day' => 'Y-m-d',
            'hour' => 'Y-m-d H:00',
            'minute' => 'Y-m-d H:i:00',
            default => throw new InvalidArgumentException('Invalid unit: ' . $this->unit),
        };
    }

    protected function getStartingDate(): CarbonImmutable
    {
        if ($this->getRange() instanceof TimeFrameEnum && $timeFrameRange = $this->getRange()?->getRange()) {
            return $timeFrameRange[0];
        }

        if ($this->startingDate && $this->getRange() === TimeFrameEnum::Custom) {
            return $this->startingDate;
        }

        $now = CarbonImmutable::now();

        return match ($this->unit) {
            'year' => $now->subYearsWithoutOverflow()
                ->firstOfYear()
                ->setTime(0, 0),
            'month' => $now->subMonthsWithoutOverflow()
                ->firstOfMonth()
                ->setTime(0, 0),
            'week' => $now->subWeeks()
                ->startOfWeek()
                ->setTime(0, 0),
            'day' => $now->subDays()
                ->setTime(0, 0),
            'hour' => $now->subHours(),
            'minute' => $now->subMinutes(),
            default => throw new InvalidArgumentException('Invalid unit: ' . $this->unit),
        };
    }

    protected function getEndingDate(): CarbonImmutable
    {
        if ($this->getRange() instanceof TimeFrameEnum && $timeFrameRange = $this->getRange()->getRange()) {
            return $timeFrameRange[1];
        }

        if ($this->endingDate && $this->getRange() === TimeFrameEnum::Custom) {
            return $this->endingDate;
        }

        return CarbonImmutable::now();
    }

    protected function resolve(): Result
    {
        $dateColumn = $this->getDateColumn();
        $startingDate = $this->getStartingDate();
        $endingDate = $this->getEndingDate();

        $expression = $this->getExpression();
        $column = $this->query->getQuery()->getGrammar()->wrap($this->column);

        $results = $this->query
            ->selectRaw("$expression as date_result, $this->type($column) as result")
            ->whereBetween($dateColumn, [$startingDate, $endingDate])
            ->groupBy('date_result')
            ->get()
            ->mapWithKeys(fn (mixed $result) => [
                $result['date_result'] => $this->transformResult($result['result']),
            ])
            ->toArray();

        $periods = collect(CarbonPeriod::create($startingDate, '1 ' . $this->unit, $endingDate))
            ->mapWithKeys(fn (CarbonInterface $date) => [
                $date->format($this->getFormat()) => 0,
            ])
            ->toArray();

        $data = collect(array_replace($periods, $results))
            ->take(-count($periods))
            ->toArray();

        $growth = count($data) < 2
            ? null
            : $this->growthRateType->getValue(previousValue: prev($data), currentValue: end($data));

        return Result::make(
            array_values($data),
            array_keys($data),
            $growth
        );
    }
}
