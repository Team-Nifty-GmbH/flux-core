<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\GrowthRateTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\OrderPosition;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Support\Widgets\ValueList;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsByUnitSold extends ValueList
{
    use IsTimeFrameAwareWidget;

    public function calculateList(): void
    {
        $query = resolve_static(OrderPosition::class, 'query')
            ->selectRaw('product_id, SUM(amount) as total_amount')
            ->groupBy('product_id')
            ->whereHas(
                'order',
                fn (Builder $query) => $query
                    ->when(
                        $this->timeFrame === TimeFrameEnum::Custom,
                        fn (Builder $query) => $query->whereBetween('invoice_date', [$this->start, $this->end]),
                        fn (Builder $query) => $query->whereBetween('invoice_date', $this->timeFrame->getRange())
                    )
                    ->revenue()
            )
            ->orderByDesc('total_amount')
            ->with('product:id,name')
            ->limit(5)
            ->get();

        $previous = resolve_static(OrderPosition::class, 'query')
            ->whereIntegerInRaw('product_id', $query->pluck('product_id'))
            ->selectRaw('product_id, SUM(amount) as total_amount')
            ->groupBy('product_id')
            ->whereHas(
                'order',
                fn (Builder $query) => $query
                    ->when(
                        $this->timeFrame === TimeFrameEnum::Custom,
                        function (Builder $query) {
                            $diff = $this->end->diffInDays($this->start);

                            return $query->whereBetween(
                                'invoice_date',
                                [$this->start->subDays($diff), $this->end->subDays($diff)]
                            );
                        },
                        fn (Builder $query) => $query->whereBetween(
                            'invoice_date',
                            $this->timeFrame->getPreviousRange()
                        )
                    )
                    ->revenue()
            )
            ->limit(5)
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
}
