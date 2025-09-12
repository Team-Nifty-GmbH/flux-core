<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Traits\Livewire\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\SupportsWidgetConfig;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class OrderMediaChart extends LineChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, SupportsWidgetConfig, Widgetable;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $collections = app(Order::class)->getRegisteredMediaCollections()
            ->pluck('name')
            ->filter()
            ->values();

        $this->series = [];
        $metrics = [];
        $colorIndex = 0;

        foreach ($collections as $collection) {
            $query = resolve_static(Media::class, 'query')
                ->where('model_type', morph_alias(Order::class))
                ->where('collection_name', $collection);

            $metric = Line::make($query)
                ->setDateColumn('created_at')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd()->endOfDay())
                ->setStartingDate($this->getStart()->startOfDay())
                ->count();

            $metrics[] = [
                'metric' => $metric,
                'collection' => $collection,
                'colorIndex' => $colorIndex++,
            ];
        }

        if (! $metrics) {
            $this->series = [];
            $this->xaxis['categories'] = [];

            return;
        }

        $this->series = collect($metrics)
            ->map(fn (array $data): array => [
                'name' => $this->getCollectionLabel(data_get($data, 'collection')),
                'color' => ChartColorEnum::forIndex(data_get($data, 'colorIndex'))->value,
                'data' => data_get($data, 'metric')->getData(),
                'collection' => data_get($data, 'collection'),
                'hidden' => data_get($this->config, 'series.' . data_get($data, 'collection') . '.hidden', false),
            ])
            ->toArray();

        $this->xaxis['categories'] = data_get($metrics, '0.metric')->getLabels();
    }

    #[Renderless]
    public function options(): array
    {
        $options = [];
        foreach ($this->series as $serie) {
            if (data_get($serie, 'collection')) {
                $options[] = [
                    'label' => data_get($serie, 'name'),
                    'method' => 'showByCollection',
                    'params' => data_get($serie, 'collection'),
                ];
            }
        }

        return $options;
    }

    #[Renderless]
    public function showByCollection(string $collection): void
    {
        $start = $this->getStart()->startOfDay();
        $end = $this->getEnd()->endOfDay();

        $orderIds = resolve_static(Media::class, 'query')
            ->where('model_type', morph_alias(Order::class))
            ->where('collection_name', $collection)
            ->whereBetween('created_at', [$start, $end])
            ->pluck('model_id')
            ->unique();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereIn('id', $orderIds),
            __('Orders with :collection media', ['collection' => $this->getCollectionLabel($collection)]) . ' ' .
            __('between :start and :end', ['start' => $start->toDateString(), 'end' => $end->toDateString()]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    #[On('apex-legend-click')]
    #[Renderless]
    public function toggleSeries(string $type, array $payload): void
    {
        if ($collection = data_get($this->series, data_get($payload, 'seriesIndex') . '.collection')) {
            $this->storeConfig(
                'series.' . $collection . '.hidden',
                ! data_get($payload, 'isHidden', true)
            );
        }
    }

    protected function getCollectionLabel(string $collection): string
    {
        return __(Str::headline($collection));
    }
}
