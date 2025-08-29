<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Livewire\Forms\EmployeeWorkTimeModelForm;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Builder;

class EmployeeWorkTimeModelList extends BaseDataTable
{
    use DataTableHasFormEdit;

    public array $enabledCols = [
        'employee.name',
        'work_time_model.name',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    public EmployeeWorkTimeModelForm $form;

    public string $model = EmployeeWorkTimeModel::class;

    protected function getTableActions(): array
    {
        return [
            'create' => true,
            'edit' => true,
            'delete' => true,
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['employee', 'workTimeModel'])
            ->orderBy('valid_from', 'desc');
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            []
        );
    }
}
