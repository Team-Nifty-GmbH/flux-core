<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsByType extends CircleChart implements HasWidgetOptions
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
        $endStates = TicketState::all()
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        $data = resolve_static(Ticket::class, 'query')
            ->whereNotIn('state', $endStates)
            ->groupBy('ticket_type_id')
            ->with('ticketType:id,name')
            ->selectRaw('ticket_type_id, COUNT(*) as total')
            ->orderBy('total', 'desc')
            ->get();

        $this->labels = $data
            ->map(fn (Ticket $ticket) => $ticket->ticketType?->name ?? __('No Type'))
            ->toArray();
        $this->series = $data->pluck('total')->toArray();
        $this->optionData = $data
            ->map(fn (Ticket $ticket) => [
                'label' => $ticket->ticketType?->name ?? __('No Type'),
                'ticket_type_id' => $ticket->ticket_type_id,
            ])
            ->toArray();
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

        $endStates = TicketState::all()
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('ticket_type_id', $ticketTypeId)
                ->whereNotIn('state', $endStates),
            __('Open tickets: :type', [
                'type' => data_get(
                    collect($this->optionData)->firstWhere('ticket_type_id', $ticketTypeId),
                    'label',
                    __('Unknown')
                ),
            ]),
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
