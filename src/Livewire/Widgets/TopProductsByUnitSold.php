<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\GrowthRateTypeEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;

class TopProductsByUnitSold extends ValueList
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
            ->orderByDesc('total_amount')
            ->with('product:id,name')
            ->limit($this->limit)
            ->get();

        $previous = resolve_static(OrderPosition::class, 'query')
            ->whereIntegerInRaw('product_id', $query->pluck('product_id'))
            ->selectRaw('product_id, SUM(amount) as total_amount')
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
            'label' => $item->product->name,
            'value' => Rounding::round($item->total_amount, 0),
            'growthRate' => GrowthRateTypeEnum::Percentage->getValue(
                $previous->get($item->product_id)?->total_amount ?? 0,
                $item->total_amount
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
            ->selectRaw('product_id, SUM(amount) as total_amount')
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
