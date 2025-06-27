<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Contracts\Targetable;
use FluxErp\Livewire\DataTables\TargetList;
use FluxErp\Livewire\Forms\TargetForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class Targets extends TargetList
{
    use DataTableHasFormEdit;

    #[Locked]
    public array $aggregateColumns = [];

    #[Locked]
    public array $aggregateTypes = [];

    #[Locked]
    public array $ownerColumns = [];

    #[DataTableForm]
    public TargetForm $target;

    #[Locked]
    public array $timeframeColumns = [];

    protected ?string $includeBefore = 'flux::livewire.settings.targets';

    public function updateAggregateColumnOptions(?string $aggregateType = null): void
    {
        $this->target->reset('aggregate_column');

        if ($aggregateType) {
            $model = morphed_model($this->target->model_type);
            $this->aggregateColumns = map_values_to_options($model::aggregateColumns($aggregateType));
        } else {
            $this->aggregateColumns = [];
        }

        $this->forceRender();
    }

    public function updateSelectableColumns(?string $modelType = null): void
    {
        $this->target->reset([
            'timeframe_column',
            'aggregate_type',
            'owner_column',
        ]);

        if ($modelType) {
            $model = morphed_model($modelType);

            $this->timeframeColumns = map_values_to_options($model::timeframeColumns());
            $this->aggregateTypes = map_values_to_options($model::aggregateTypes());
            $this->ownerColumns = map_values_to_options($model::ownerColumns());
        }

        $this->forceRender();
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'modelTypes' => collect(Relation::morphMap())
                    ->filter(fn (string $model) => is_a($model, Targetable::class, true))
                    ->map(fn (string $class, string $alias) => [
                        'label' => __(Str::headline($alias)),
                        'value' => $alias,
                    ])
                    ->values()
                    ->toArray(),
            ]
        );
    }
}
