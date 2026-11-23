<?php

namespace FluxErp\Livewire\Product;

use Carbon\CarbonImmutable;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\DataTables\StockPostingList as BaseStockPostingList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ExpiringStockList extends BaseStockPostingList
{
    protected const DAYS_RULES = 'required|integer|min:1|max:3650';

    protected const DEFAULT_DAYS = 30;

    protected const TIME_FRAMES = [
        TimeFrameEnum::Today,
        TimeFrameEnum::ThisWeek,
        TimeFrameEnum::ThisMonth,
        TimeFrameEnum::ThisQuarter,
        TimeFrameEnum::ThisYear,
        TimeFrameEnum::Custom,
    ];

    #[Url]
    public int $days = self::DEFAULT_DAYS;

    public array $enabledCols = [
        'product.name',
        'lot.lot_number',
        'lot.expires_at',
        'warehouse.name',
        'storage_area.code',
        'remaining_stock',
    ];

    #[Url]
    public string $timeFrame = TimeFrameEnum::ThisMonth;

    protected ?string $includeBefore = 'flux::livewire.product.expiring-stock-list';

    public function booted(): void
    {
        $this->resetInvalidWindow();
    }

    public function updatedDays(): void
    {
        $this->resetInvalidWindow();

        $this->loadData();
    }

    public function updatedTimeFrame(): void
    {
        $this->resetInvalidWindow();

        $this->loadData();
    }

    protected function getBuilder(Builder $builder): Builder
    {
        $today = CarbonImmutable::today(config('flux.display_timezone') ?? config('app.timezone'));

        return match ($this->timeFrame) {
            TimeFrameEnum::Today => $builder->expiringUntil($today),
            TimeFrameEnum::ThisWeek => $builder->expiringUntil($today->endOfWeek()),
            TimeFrameEnum::ThisMonth => $builder->expiringUntil($today->endOfMonth()),
            TimeFrameEnum::ThisQuarter => $builder->expiringUntil($today->endOfQuarter()),
            TimeFrameEnum::ThisYear => $builder->expiringUntil($today->endOfYear()),
            default => $builder->expiringWithin($this->days),
        };
    }

    protected function resetInvalidWindow(): void
    {
        if (! in_array($this->timeFrame, static::TIME_FRAMES, true)) {
            $this->timeFrame = TimeFrameEnum::ThisMonth;
        }

        if (validator(['days' => $this->days], ['days' => static::DAYS_RULES])->fails()) {
            $this->days = static::DEFAULT_DAYS;
        }
    }
}
