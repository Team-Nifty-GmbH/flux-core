<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Traits\Livewire\Widget\Widgetable;

class DepartmentHeadcountChart extends BarChart
{
    use Widgetable;

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '75%',
        ],
    ];

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 2;
    }

    public function calculateChart(): void
    {
        $now = now();

        $departments = resolve_static(EmployeeDepartment::class, 'query')
            ->where('is_active', true)
            ->withCount([
                'employees' => fn ($query) => $query->employed($now),
            ])
            ->having('employees_count', '>', 0)
            ->orderByDesc('employees_count')
            ->get(['id', 'name']);

        if ($departments->isEmpty()) {
            $this->series = [];
            $this->xaxis = null;

            return;
        }

        $this->xaxis = [
            'categories' => $departments->pluck('name')->toArray(),
        ];

        $this->series = [
            [
                'name' => __('Employees'),
                'data' => $departments->pluck('employees_count')->toArray(),
                'color' => ChartColorEnum::Blue,
            ],
        ];
    }
}
