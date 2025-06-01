<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\GrowthRateTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;

class TopProductsByRevenue extends ValueList
{
    use IsTimeFrameAwareWidget;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateList(): void
    {
        $query = $this->query()
            ->orderByDesc('total_net_price')
            ->with('product:id,name')
            ->limit($this->limit)
            ->get();

        $previous = resolve_static(OrderPosition::class, 'query')
            ->whereIntegerInRaw('product_id', $query->pluck('product_id'))
            ->selectRaw('product_id, SUM(total_net_price) as total_net_price')
            ->groupBy('product_id')
            ->whereHas(
                'order',
                fn (Builder $query) => $query
                    ->whereBetween('invoice_date', [$this->getStartPrevious(), $this->getEndPrevious()])
                    ->revenue()
            )
            ->limit($this->limit)
            ->get()
            ->keyBy('product_id');

        $this->items = $query->map(fn ($item) => [
            'label' => $item->product?->name,
            'value' => Number::abbreviate(Rounding::round($item->total_net_price ?? 0, 0))
                . ' ' . resolve_static(Currency::class, 'default')->symbol,
            'growthRate' => GrowthRateTypeEnum::Percentage->getValue(
                $previous->get($item->product_id)?->total_net_price ?? 0,
                $item->total_net_price ?? 0
            ),
        ])->toArray();
    }

    #[Renderless]
    public function hasMore(): bool
    {
        return $this->limit < $this->query()->count();
    }

    #[Renderless]
    public function showMore(): void
    {
        $this->limit += 10;

        $this->calculateList();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateList',
        ];
    }

    protected function hasLoadMore(): bool
    {
        return true;
    }

    protected function query(): Builder
    {
        return resolve_static(OrderPosition::class, 'query')
            ->selectRaw('product_id, SUM(total_net_price) as total_net_price')
            ->groupBy('product_id')
            ->whereHas(
                'order',
                fn (Builder $query) => $query
                    ->whereBetween('invoice_date', [$this->getStart(), $this->getEnd()])
                    ->revenue()
            )
            ->whereHas('product');
    }
}
