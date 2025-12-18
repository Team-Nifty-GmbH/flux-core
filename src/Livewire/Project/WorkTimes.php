<?php

namespace FluxErp\Livewire\Project;

use FluxErp\Livewire\DataTables\WorkTimeList;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;

class WorkTimes extends WorkTimeList
{
    #[Modelable]
    public int $projectId;

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->projectId;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHasMorph(
            'model',
            [
                Project::class,
                Task::class,
            ],
            fn (Builder $query, string $type) => $query
                ->when($type === Project::class, fn (Builder $query) => $query->whereKey($this->projectId))
                ->when($type === Task::class, fn (Builder $query) => $query->where('project_id', $this->projectId))
        );
    }
}
