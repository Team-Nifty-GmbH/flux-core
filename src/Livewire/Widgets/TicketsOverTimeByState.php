<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Support\Metrics\Charts\Bar;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsOverTimeByState extends BarChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public array $optionData = [];

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function getDefaultWidth(): int
    {
        return 3;
    }

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
        $states = TicketState::all();

        $this->series = [];
        $this->optionData = [];
        $categories = null;

        foreach ($states as $stateName => $stateClass) {
            $query = resolve_static(Ticket::class, 'query')
                ->where('state', $stateName);

            $result = Bar::make($query)
                ->setDateColumn('created_at')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd())
                ->setStartingDate($this->getStart())
                ->count();

            $color = resolve_static(
                ChartColorEnum::class,
                'fromColor',
                ['colorName' => (new $stateClass(''))->color()]
            );

            $this->series[] = [
                'name' => __(Str::headline($stateName)),
                'color' => $color,
                'data' => $result->getData(),
                'growthRate' => $result->getGrowthRate(),
            ];

            $this->optionData[] = [
                'label' => __(Str::headline($stateName)),
                'state' => $stateName,
            ];

            $categories ??= $result->getLabels();
        }

        $this->xaxis = [
            'categories' => $categories ?? [],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'label'),
                'method' => 'show',
                'params' => ['state' => data_get($data, 'state')],
            ],
            $this->optionData
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $state = data_get($params, 'state');
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('state', $state)
                ->whereBetween('created_at', [$start, $end]),
            __('Tickets: :state', ['state' => __(Str::headline($state))]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }
}
