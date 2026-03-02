<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Support\Metrics\Charts\Bar;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsOverTime extends BarChart implements HasWidgetOptions
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
        $ticketTypes = resolve_static(TicketType::class, 'query')
            ->get(['id', 'name']);

        $this->series = [];
        $this->optionData = [];
        $categories = null;
        $colorIndex = 0;

        foreach ($ticketTypes as $ticketType) {
            $query = resolve_static(Ticket::class, 'query')
                ->where('ticket_type_id', $ticketType->getKey());

            $result = Bar::make($query)
                ->setDateColumn('created_at')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd())
                ->setStartingDate($this->getStart())
                ->count();

            $this->series[] = [
                'name' => $ticketType->name,
                'color' => resolve_static(
                    ChartColorEnum::class,
                    'forIndex',
                    ['index' => $colorIndex]
                )
                    ->value,
                'data' => $result->getData(),
                'growthRate' => $result->getGrowthRate(),
            ];

            $this->optionData[] = [
                'label' => $ticketType->name,
                'ticket_type_id' => $ticketType->getKey(),
            ];

            $categories ??= $result->getLabels();
            $colorIndex++;
        }

        $noTypeResult = Bar::make(
            resolve_static(Ticket::class, 'query')
                ->whereNull('ticket_type_id')
        )
            ->setDateColumn('created_at')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->count();

        if (array_sum(array_map('floatval', $noTypeResult->getData())) > 0) {
            $this->series[] = [
                'name' => __('No Type'),
                'color' => resolve_static(
                    ChartColorEnum::class,
                    'forIndex',
                    ['index' => $colorIndex]
                )
                    ->value,
                'data' => $noTypeResult->getData(),
                'growthRate' => $noTypeResult->getGrowthRate(),
            ];

            $this->optionData[] = [
                'label' => __('No Type'),
                'ticket_type_id' => null,
            ];
        }

        $categories ??= $noTypeResult->getLabels();

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
                'params' => ['ticket_type_id' => data_get($data, 'ticket_type_id')],
            ],
            $this->optionData
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $ticketTypeId = data_get($params, 'ticket_type_id');
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('ticket_type_id', $ticketTypeId)
                ->whereBetween('created_at', [$start, $end]),
            __('Tickets: :type', [
                'type' => data_get(
                    collect($this->optionData)->firstWhere('ticket_type_id', $ticketTypeId),
                    'label',
                    __('Unknown')
                ),
            ]) . ' ' . __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }
}
