<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use Illuminate\Database\Eloquent\Builder;

class EmployeeWorkTimeModelList extends BaseDataTable
{
    public array $enabledCols = [
        'employee.name',
        'work_time_model.name',
        'valid_from',
        'valid_until',
    ];

    protected string $model = EmployeeWorkTimeModel::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['employee', 'workTimeModel'])
            ->orderBy('valid_from', 'desc');
    }
}
