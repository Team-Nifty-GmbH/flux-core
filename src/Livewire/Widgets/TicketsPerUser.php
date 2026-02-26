<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class TicketsPerUser extends BarChart implements HasWidgetOptions
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'bar',
        'stacked' => true,
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '75%',
        ],
    ];

    public array $optionData = [];

    public bool $showTotals = false;

    public ?array $yaxis = [
        'labels' => ['show' => true],
    ];

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

        $openStates = $allStates
            ->reject(fn (string $state) => $state::$isEndState);

        $users = resolve_static(User::class, 'query')
            ->whereHas('tickets', fn (Builder $query) => $query->whereNotIn('state', $endStates))
            ->withCount(['tickets' => fn (Builder $query) => $query->whereNotIn('state', $endStates)])
            ->orderByDesc('tickets_count')
            ->limit(15)
            ->get();

        if ($users->isEmpty()) {
            $this->labels = [];
            $this->series = [];

            return;
        }

        $this->labels = $users
            ->map(fn (User $user) => $user->getLabel())
            ->toArray();
        $this->optionData = $users
            ->map(fn (User $user) => [
                'label' => $user->getLabel(),
                'id' => $user->getKey(),
            ])
            ->toArray();

        $userKeys = $users
            ->map(fn (User $user) => $user->getKey())
            ->toArray();

        $breakdown = resolve_static(Ticket::class, 'query')
            ->join('ticket_user', 'tickets.id', '=', 'ticket_user.ticket_id')
            ->whereNotIn('tickets.state', $endStates)
            ->whereIntegerInRaw('ticket_user.user_id', $userKeys)
            ->groupBy('ticket_user.user_id', 'tickets.state')
            ->selectRaw('ticket_user.user_id, tickets.state, COUNT(*) as total')
            ->get()
            ->groupBy('user_id');

        $this->series = [];

        foreach ($openStates as $stateName => $stateClass) {
            $data = $users->map(function (User $user) use ($breakdown, $stateName) {
                $userBreakdown = $breakdown->get($user->getKey(), collect());

                return (int) ($userBreakdown->firstWhere('state', $stateName)?->total ?? 0);
            })
                ->toArray();

            if (array_sum($data) > 0) {
                $color = resolve_static(
                    ChartColorEnum::class,
                    'fromColor',
                    ['colorName' => app($stateClass, ['model' => null])->color()]
                );

                $this->series[] = [
                    'name' => __(Str::headline($stateName)),
                    'color' => $color,
                    'data' => $data,
                ];
            }
        }
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'label'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'name' => data_get($data, 'label'),
                ],
            ],
            $this->optionData
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $endStates = TicketState::all()
            ->filter(fn (string $state) => $state::$isEndState)
            ->keys()
            ->toArray();

        $id = data_get($params, 'id');
        $name = data_get($params, 'name');

        SessionFilter::make(
            Livewire::new(resolve_static(TicketList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereHas('users', fn (Builder $userQuery) => $userQuery->whereKey($id))
                ->whereNotIn('state', $endStates),
            __('Open tickets for :user', ['user' => $name]),
        )
            ->store();

        $this->redirectRoute('tickets', navigate: true);
    }
}
