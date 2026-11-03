<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\SalaryComponent;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Support\Collection;

class PersonnelCostsByDepartmentChart extends BarChart
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

    public function calculateChart(): void
    {
        $departments = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->with([
                'employeeDepartment:id,name',
                'salaryComponents:id,employee_id,amount,payment_interval,valid_from,valid_until',
                'workTimeModelHistory.workTimeModel:id,weekly_hours',
            ])
            ->get([
                'id',
                'employee_department_id',
                'salary_type',
                'base_salary',
                'hourly_rate',
                'employer_contribution_rate',
            ])
            ->groupBy(
                fn (Employee $employee): string => $employee->employeeDepartment?->name ?? __('No Department')
            )
            ->map(fn (Collection $employees): float => (float) $employees->reduce(
                fn (string $carry, Employee $employee): string => bcadd(
                    $carry,
                    $employee->getMonthlyPersonnelCosts()['total']
                ),
                '0'
            ))
            ->sortDesc();

        if ($departments->isEmpty()) {
            $this->series = [];
            $this->xaxis = null;

            return;
        }

        $this->xaxis = [
            'categories' => $departments->keys()->toArray(),
        ];

        $this->series = [
            [
                'name' => __('Personnel Costs'),
                'data' => $departments
                    ->map(fn (float $total): float => round($total, 2))
                    ->values()
                    ->toArray(),
                'color' => ChartColorEnum::Blue,
            ],
        ];
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(Employee::class) => [
                Employee::class => ['created', 'updated', 'deleted'],
            ],
            $this->modelChannel(EmployeeDepartment::class) => [
                EmployeeDepartment::class => ['updated', 'deleted'],
            ],
            $this->modelChannel(SalaryComponent::class) => [
                SalaryComponent::class => ['created', 'updated', 'deleted'],
            ],
        ];
    }
}
