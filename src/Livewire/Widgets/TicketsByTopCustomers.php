<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\Chart;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
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

        $topCustomers = resolve_static(Address::class, 'query')
            ->join('tickets', function (JoinClause $join): void {
                $join->on('addresses.id', '=', 'tickets.authenticatable_id')
                    ->where('tickets.authenticatable_type', morph_alias(Address::class));
            })
            ->whereBetween('tickets.created_at', $dateRange)
            ->whereNull('tickets.deleted_at')
            ->groupBy('contact_id')
            ->selectRaw('contact_id, COUNT(*) as total')
            ->with('contact')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        if ($topCustomers->isEmpty()) {
            $this->labels = [];
            $this->series = [];

            return;
        }

        $this->labels = $topCustomers
            ->pluck('contact')
            ->map(fn (Contact $contact) => $contact->getLabel() ?? __('Unknown'))
            ->values()
            ->toArray();

        $this->optionData = $topCustomers
            ->mapWithKeys(fn (Address $address) => [
                $address->contact_id => [
                    'label' => $address->contact?->getLabel() ?? __('Unknown'),
                    'authenticatable_type' => $address->getMorphClass(),
                    'authenticatable_ids' => $address->contact?->addresses()->pluck('addresses.id')->toArray() ?? [],
                ],
            ])
            ->toArray();

        $order = array_flip(array_keys($this->optionData));
        $ticketsByCustomer = resolve_static(Ticket::class, 'query')
            ->where('authenticatable_type', morph_alias(Address::class))
            ->whereBetween('created_at', $dateRange)
            ->where(function (Builder $query): void {
                foreach ($this->optionData as $option) {
                    $query->orWhereIntegerInRaw('authenticatable_id', data_get($option, 'authenticatable_ids'));
                }
            })
            ->selectRaw('ticket_type_id, authenticatable_id')
            ->get()
            ->groupBy(fn (Ticket $ticket) => array_find_key(
                $this->optionData,
                fn ($option) => in_array($ticket->authenticatable_id, data_get($option, 'authenticatable_ids'))
            ))
            ->sortKeysUsing(fn ($a, $b) => $order[$a] <=> $order[$b])
            ->map(fn (Collection $tickets) => $tickets->countBy('ticket_type_id')->toArray());

        $ticketTypes = resolve_static(TicketType::class, 'query')
            ->get(['id', 'name']);
        $colorIndex = 0;
        $this->series = [];

        foreach ($ticketTypes as $ticketType) {
            $data = $ticketsByCustomer
                ->map(fn (array $item) => array_key_exists($ticketType->id, $item) ? $item[$ticketType->id] : 0)
                ->values()
                ->toArray();

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

        $noTypeData = $ticketsByCustomer
            ->map(fn (array $item) => array_key_exists('', $item) ? $item[''] : 0)
            ->values()
            ->toArray();

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
                    'authenticatable_ids' => data_get($data, 'authenticatable_ids'),
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
        $authenticatableIds = data_get($params, 'authenticatable_ids');
        $name = data_get($params, 'name');

        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('authenticatable_type', $authenticatableType)
                ->whereIntegerInRaw('authenticatable_id', $authenticatableIds)
                ->whereBetween('created_at', [$start, $end]),
            __('Tickets by :customer', ['customer' => $name]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }
}
