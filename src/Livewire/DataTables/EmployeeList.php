<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Renderless;

class EmployeeList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'name',
        'job_title',
        'phone_mobile',
        'email',
        'health_insurance',
        'probation_period_until',
        'fixed_term_contract_until',
        'is_active',
    ];

    public bool $hasNoRedirect = true;

    protected string $model = Employee::class;

    #[Renderless]
    public function getAvailableCols(): array
    {
        return array_values(array_diff(parent::getAvailableCols(), app($this->model)->getHidden()));
    }

    protected function buildSearch(bool $unpaginated = false): Builder
    {
        $hidden = app($this->model)->getHidden();

        $this->enabledCols = array_values(array_diff($this->enabledCols, $hidden));
        $this->aggregatableCols = array_map(
            fn (array|string $columns): array => array_values(array_diff(Arr::wrap($columns), $hidden)),
            $this->aggregatableCols
        );
        $this->userFilters = $this->withoutHiddenColumns($this->userFilters, $hidden);
        $this->userMultiSort = array_values(
            array_filter(
                $this->userMultiSort,
                fn (array $sort): bool => ! in_array(data_get($sort, 'column'), $hidden, true)
            )
        );

        if (in_array($this->userOrderBy, $hidden, true)) {
            $this->userOrderBy = '';
        }

        if (in_array($this->groupBy, $hidden, true)) {
            $this->groupBy = null;
        }

        return parent::buildSearch($unpaginated);
    }

    protected function withoutHiddenColumns(array $filters, array $hidden): array
    {
        foreach ($filters as $key => $filter) {
            if (in_array($key, $hidden, true)
                || (is_array($filter) && in_array(data_get($filter, 'column'), $hidden, true))
            ) {
                unset($filters[$key]);
            } elseif (is_array($filter)) {
                $filters[$key] = $this->withoutHiddenColumns($filter, $hidden);
            }
        }

        return $filters;
    }
}
