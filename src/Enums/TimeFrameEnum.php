<?php

namespace FluxErp\Enums;

use Carbon\Carbon;
use FluxErp\Enums\Traits\EnumTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

enum TimeFrameEnum: string
{
    use EnumTrait;

    case LastWeek = 'Last Week';
    case LastMonth = 'Last Month';
    case LastYear = 'Last Year';
    case ThisWeek = 'This Week';
    case ThisMonth = 'This Month';
    case ThisYear = 'This Year';
    case AllTime = 'All Time';
    case Custom = 'Custom';

    public function dateQueryParameters(string $dateField): array
    {
        return match ($this) {
            self::LastWeek => [
                'column' => $dateField,
                'operator' => '>=',
                'value' => Carbon::now()->subWeek(),
            ],
            self::LastMonth => [
                'column' => $dateField,
                'operator' => '>=',
                'value' => Carbon::now()->subMonth(),
            ],
            self::LastYear => [
                'column' => $dateField,
                'operator' => '>=',
                'value' => Carbon::now()->subYear(),
            ],
            self::ThisWeek => [
                'column' => $dateField,
                'operator' => 'between',
                'value' => [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ],
            ],
            self::ThisMonth => [
                'column' => $dateField,
                'operator' => 'between',
                'value' => [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ],
            ],
            self::ThisYear => [
                'column' => $dateField,
                'operator' => 'between',
                'value' => [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear(),
                ],
            ],
            self::AllTime, self::Custom => [],
            default => throw new \InvalidArgumentException('Invalid time frame'),
        };
    }

    public function groupQuery(Builder $builder, string $dateField, string $groupByKey = 'group_key'): Builder
    {
        return $builder->select(
            DB::raw(
                'DATE_FORMAT(' . $dateField . ', \'' . $this->groupKeyFormatter($dateField) . '\') as '
                . $groupByKey
            )
        )
            ->groupBy(...$this->groupBy($dateField, $groupByKey))
            ->orderBy($groupByKey);
    }

    private function groupBy(string $dateField, string $groupByKey = 'group_key'): array
    {
        return array_merge(match ($this) {
            self::LastWeek, self::ThisWeek, self::ThisMonth, self::LastMonth => [$dateField],
            self::LastYear, self::ThisYear => [
                DB::raw('YEAR(' . $dateField . ')'),
                DB::raw('MONTH(' . $dateField . ')'),
            ],
            self::AllTime => [DB::raw('YEAR(' . $dateField . ')')],
            default => throw new \InvalidArgumentException('Invalid time frame'),
        }, [$groupByKey]);
    }

    private function groupKeyFormatter(string $dateField): string
    {
        return match ($this) {
            self::LastWeek, self::ThisWeek, self::ThisMonth, self::LastMonth => '%Y-%m-%d',
            self::LastYear, self::ThisYear => '%Y-%m',
            self::AllTime => '%Y',
            default => throw new \InvalidArgumentException('Invalid time frame'),
        };
    }
}
