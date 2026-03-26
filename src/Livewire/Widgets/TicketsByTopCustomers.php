<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\Chart;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsByTopCustomers extends Chart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public array $optionData = [];

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.support.widgets.charts.chart');
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $dateRange = [
            $this->getStart()->toDateTimeString(),
            $this->getEnd()->toDateTimeString(),
        ];

        $topCustomers = resolve_static(Ticket::class, 'query')
            ->where('authenticatable_type', morph_alias(Address::class))
            ->whereBetween('created_at', $dateRange)
            ->groupBy('authenticatable_type', 'authenticatable_id')
            ->selectRaw('authenticatable_type, authenticatable_id, COUNT(*) as total')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        if ($topCustomers->isEmpty()) {
            $this->labels = [];
            $this->series = [];

            return;
        }

        $names = collect();
        $topCustomers
            ->groupBy('authenticatable_type')
            ->each(function (Collection $rows, string $type) use ($names): void {
                $modelClass = morphed_model($type);

                if (is_null($modelClass)) {
                    return;
                }

                resolve_static($modelClass, 'query')
                    ->whereKey($rows->pluck('authenticatable_id')->toArray())
                    ->pluck('name', 'id')
                    ->each(fn (string $name, int $id) => $names->put($type . ':' . $id, $name));
            });

        $customerKeys = $topCustomers
            ->map(fn (Ticket $ticket) => $ticket->authenticatable_type . ':' . $ticket->authenticatable_id)
            ->toArray();

        $this->labels = array_map(
            fn (string $key) => $names->get($key) ?? __('Unknown'),
            $customerKeys
        );

        $this->optionData = $topCustomers
            ->map(fn (Ticket $ticket) => [
                'label' => $names->get(
                    $ticket->authenticatable_type . ':' . $ticket->authenticatable_id
                ) ?? __('Unknown'),
                'authenticatable_type' => $ticket->authenticatable_type,
                'authenticatable_id' => $ticket->authenticatable_id,
            ])
            ->toArray();

        $ticketsByCustomer = resolve_static(Ticket::class, 'query')
            ->where('authenticatable_type', morph_alias(Address::class))
            ->whereBetween('created_at', $dateRange)
            ->where(function (Builder $query) use ($topCustomers): void {
                foreach ($topCustomers as $customer) {
                    $query->orWhere(
                        fn (Builder $subQuery) => $subQuery
                            ->where('authenticatable_type', $customer->authenticatable_type)
                            ->where('authenticatable_id', $customer->authenticatable_id)
                    );
                }
            })
            ->groupBy('authenticatable_type', 'authenticatable_id', 'ticket_type_id')
            ->selectRaw('authenticatable_type, authenticatable_id, ticket_type_id, COUNT(*) as total')
            ->get()
            ->groupBy(fn (Ticket $ticket) => $ticket->authenticatable_type . ':' . $ticket->authenticatable_id);

        $ticketTypes = resolve_static(TicketType::class, 'query')
            ->get(['id', 'name']);
        $colorIndex = 0;
        $this->series = [];

        foreach ($ticketTypes as $ticketType) {
            $data = array_map(
                function (string $key) use ($ticketsByCustomer, $ticketType): int {
                    return (int) ($ticketsByCustomer->get($key, collect())
                        ->firstWhere('ticket_type_id', $ticketType->getKey())
                        ?->total ?? 0);
                },
                $customerKeys
            );

            if (array_sum($data) > 0) {
                $this->series[] = [
                    'name' => $ticketType->name,
                    'color' => resolve_static(
                        ChartColorEnum::class,
                        'forIndex',
                        ['index' => $colorIndex]
                    )
                        ->value,
                    'data' => $data,
                ];
            }

            $colorIndex++;
        }

        $noTypeData = array_map(
            function (string $key) use ($ticketsByCustomer): int {
                return (int) ($ticketsByCustomer->get($key, collect())
                    ->firstWhere('ticket_type_id', null)
                    ?->total ?? 0);
            },
            $customerKeys
        );

        if (array_sum($noTypeData) > 0) {
            $this->series[] = [
                'name' => __('No Type'),
                'color' => resolve_static(
                    ChartColorEnum::class,
                    'forIndex',
                    ['index' => $colorIndex]
                )
                    ->value,
                'data' => $noTypeData,
            ];
        }

        $this->plotOptions = [
            'bar' => [
                'horizontal' => true,
            ],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'label'),
                'method' => 'show',
                'params' => [
                    'authenticatable_type' => data_get($data, 'authenticatable_type'),
                    'authenticatable_id' => data_get($data, 'authenticatable_id'),
                    'name' => data_get($data, 'label'),
                ],
            ],
            $this->optionData
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $authenticatableType = data_get($params, 'authenticatable_type');
        $authenticatableId = data_get($params, 'authenticatable_id');
        $name = data_get($params, 'name');

        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('authenticatable_type', $authenticatableType)
                ->where('authenticatable_id', $authenticatableId)
                ->whereBetween('created_at', [$start, $end]),
            __('Tickets by :customer', ['customer' => $name]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }
}
