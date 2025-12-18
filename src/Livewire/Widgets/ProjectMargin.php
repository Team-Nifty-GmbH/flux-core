<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Project\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class ProjectMargin extends ValueBox implements HasWidgetOptions
{
    public ?int $projectId = null;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $this->sum = Number::format($this->getOrderQuery()->sum('margin'), 2)
            . ' ' . resolve_static(Currency::class, 'default')->symbol;
    }

    #[Renderless]
    public function options(): array
    {
        return [
            [
                'label' => __('Show Orders'),
                'method' => 'show',
                'params' => null,
            ],
        ];
    }

    #[Renderless]
    public function show(): void
    {
        $projectName = resolve_static(Project::class, 'query')
            ->whereKey($this->projectId)
            ->value('name');

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $this->getOrderQuery($query),
            __('Orders for Project :name', ['name' => $projectName])
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    protected function getOrderQuery(?Builder $query = null): Builder
    {
        $query = $query ?? resolve_static(Order::class, 'query');

        return $query->where(function (Builder $query): void {
            $query->whereRelation('projects', 'id', $this->projectId)
                ->orWhereRelation('parent.projects', 'id', $this->projectId);
        });
    }

    protected function icon(): string
    {
        return 'banknotes';
    }
}
