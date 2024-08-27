<?php

namespace FluxErp\Support\Metrics;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Support\Metrics\Results\Result;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Trend extends Metric
{
    protected string $unit;

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            parent::__call($method, $parameters);
        }

        $call = explode('By', $method);
        $rangeUnit = $this->range?->getUnit();

        $unit = strtolower($call[1] ?? '') === 'range' && $rangeUnit
            ? $rangeUnit
            : Str::of($call[1] ?? '')->singular()->lower()->toString();

        // if the range is custom we need to determinate the unit based on starting and ending date
        if ($this->range === TimeFrameEnum::Custom) {
            $diff = $this->startingDate->diffInDays($this->endingDate);

            $unit = match (true) {
                $diff < 30 => 'day',
                $diff < 365 => 'month',
                default => 'year',
            };
        }

        if (! in_array($unit, ['year', 'month', 'week', 'day', 'hour', 'minute'], true)) {
            throw new InvalidArgumentException('Invalid unit: ' . $unit);
        }

        if (! in_array($call[0], ['min', 'max', 'sum', 'avg', 'count'], true)) {
            throw new InvalidArgumentException('Invalid type: ' . $call[0]);
        }

        return $this->setType($call[0], $unit, $parameters[0] ?? '*');
    }

    protected function setType(string $type, string $unit, string $column): Result
    {
        $this->type = $type;
        $this->unit = $unit;
        $this->column = $column;

        return $this->resolve();
    }

    protected function getExpression(): string
    {
        $grammar = $this->query->getQuery()->getGrammar();
        $driver = $this->query->getConnection()->getDriverName(); // @phpstan-ignore-line
        $dateColumn = $grammar->wrap($this->getDateColumn());

        if (! in_array($this->unit, ['year', 'month', 'week', 'day', 'hour', 'minute'], true)) {
            throw new InvalidArgumentException('Invalid unit: ' . $this->unit);
        }

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
            default => throw new InvalidArgumentException('Laravel Easy Metrics is not supported for this database.')
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
        $range = $this->getRange() - 1;

        return match ($this->unit) {
            'year' => $now
                ->subYearsWithoutOverflow($range)
                ->firstOfYear()
                ->setTime(0, 0),
            'month' => $now
                ->subMonthsWithoutOverflow($range)
                ->firstOfMonth()
                ->setTime(0, 0),
            'week' => $now
                ->subWeeks($range)
                ->startOfWeek()
                ->setTime(0, 0),
            'day' => $now
                ->subDays($range)
                ->setTime(0, 0),
            'hour' => $now
                ->subHours($range),
            'minute' => $now
                ->subMinutes($range),
            default => throw new InvalidArgumentException('Invalid unit: ' . $this->unit),
        };
    }

    protected function getEndingDate(): CarbonImmutable
    {
        if ($this->getRange() instanceof TimeFrameEnum && $timeFrameRange = $this->getRange()?->getRange()) {
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
