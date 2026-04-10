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
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsByTopCustomersByState extends Chart implements HasWidgetOptions
{
    use Widgetable;

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

    public function calculateChart(): void
    {
        $allStates = TicketState::all();
        $endStates = $this->getEndStates($allStates);

        $topCustomers = resolve_static(Address::class, 'query')
            ->join('tickets', function (JoinClause $join): void {
                $join->on('addresses.id', '=', 'tickets.authenticatable_id')
                    ->where('tickets.authenticatable_type', morph_alias(Address::class));
            })
            ->whereNotIn('tickets.state', $endStates)
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
            ->whereNotIn('state', $endStates)
            ->where(function (Builder $query): void {
                foreach ($this->optionData as $option) {
                    $query->orWhereIntegerInRaw('authenticatable_id', data_get($option, 'authenticatable_ids'));
                }
            })
            ->selectRaw('authenticatable_id, state')
            ->get()
            ->groupBy(fn (Ticket $ticket) => array_find_key(
                $this->optionData,
                fn ($option) => in_array($ticket->authenticatable_id, data_get($option, 'authenticatable_ids'))
            ))
            ->sortKeysUsing(fn ($a, $b) => $order[$a] <=> $order[$b])
            ->map(fn (Collection $tickets) => $tickets->map(fn ($ticket) => (string) $ticket->state)->countBy()->all());

        $this->series = [];
        $usedStates = array_keys($ticketsByCustomer->reduce(
            fn (array $carry, array $item) => array_merge($carry, $item),
            []
        ));
        sort($usedStates);

        foreach ($usedStates as $state) {
            $stateClass = $allStates->get($state);

            if ($stateClass) {
                $color = resolve_static(
                    ChartColorEnum::class,
                    'fromColor',
                    ['colorName' => app($stateClass, ['model' => null])->color()]
                );
            } else {
                $color = resolve_static(
                    ChartColorEnum::class,
                    'forKey',
                    ['key' => $state]
                )
                    ->value;
            }

            $data = $ticketsByCustomer
                ->map(fn (array $item) => array_key_exists($state, $item) ? $item[$state] : 0)
                ->values()
                ->toArray();

            if (array_sum($data) > 0) {
                $this->series[] = [
                    'name' => __(Str::headline($state)),
                    'color' => $color,
                    'data' => $data,
                ];
            }
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

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('authenticatable_type', $authenticatableType)
                ->where('authenticatable_id', $authenticatableId)
                ->whereNotIn('state', $this->getEndStates()),
            __('Tickets by :customer', ['customer' => $name]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }

    protected function getEndStates(?Collection $allStates = null): array
    {
        return ($allStates ?? TicketState::all())
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();
    }
}
