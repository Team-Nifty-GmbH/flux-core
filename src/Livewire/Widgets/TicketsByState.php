<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsByState extends CircleChart implements HasWidgetOptions
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public array $optionData = [];

    public bool $showTotals = false;

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function calculateChart(): void
    {
        $allStates = TicketState::all();

        $endStates = $allStates
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        $data = resolve_static(Ticket::class, 'query')
            ->whereNotIn('state', $endStates)
            ->groupBy('state')
            ->selectRaw('state, COUNT(*) as total')
            ->orderBy('total', 'desc')
            ->get();

        $this->labels = $data
            ->map(fn (Ticket $ticket) => __(Str::headline((string) $ticket->state)))
            ->toArray();
        $this->series = $data->pluck('total')->toArray();
        $this->optionData = $data
            ->map(fn (Ticket $ticket) => [
                'label' => __(Str::headline((string) $ticket->state)),
                'state' => (string) $ticket->state,
            ])
            ->toArray();
        $this->colors = $data->map(function (Ticket $ticket) use ($allStates) {
            $stateClass = $allStates->get((string) $ticket->state);

            if ($stateClass) {
                return resolve_static(
                    ChartColorEnum::class,
                    'fromColor',
                    ['colorName' => app($stateClass, ['model' => null])->color()]
                );
            }

            return resolve_static(
                ChartColorEnum::class,
                'forKey',
                ['key' => (string) $ticket->state]
            )
                ->value;
        })
            ->toArray();
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

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->where('state', $state),
            __('Tickets: :state', ['state' => __(Str::headline($state))]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Total'),
                        ],
                    ],
                ],
            ],
        ];
    }
}
