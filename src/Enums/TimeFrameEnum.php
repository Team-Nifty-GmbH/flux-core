<?php

namespace FluxErp\Enums;

use Carbon\Carbon;
use FluxErp\Enums\Traits\EnumTrait;

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

    public function dateQueryParameters(): array
    {
        return match ($this) {
            self::LastWeek => ['invoice_date', '>=', Carbon::now()->subWeek()],
            self::LastMonth => ['invoice_date', '>=', Carbon::now()->subMonth()],
            self::LastYear => ['invoice_date', '>=', Carbon::now()->subYear()],
            self::ThisWeek => ['invoice_date', 'between', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]],
            self::ThisMonth => ['invoice_date', 'between', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]],
            self::ThisYear => ['invoice_date', 'between', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]],
            self::AllTime => [],
            default => throw new \Exception('Invalid time frame'),
        };
    }
}
