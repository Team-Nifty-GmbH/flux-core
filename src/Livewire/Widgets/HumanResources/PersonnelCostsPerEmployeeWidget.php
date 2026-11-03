<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\Employee;
use FluxErp\Models\SalaryComponent;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PersonnelCostsPerEmployeeWidget extends Component
{
    use Widgetable;

    #[Locked]
    public array $employees = [];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.personnel-costs-per-employee');
    }

    public function loadData(): void
    {
        $this->employees = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->with([
                'employeeDepartment:id,name',
                'salaryComponents:id,employee_id,amount,payment_interval,valid_from,valid_until',
                'workTimeModelHistory.workTimeModel:id,weekly_hours',
            ])
            ->get([
                'id',
                'employee_department_id',
                'name',
                'salary_type',
                'base_salary',
                'hourly_rate',
                'employer_contribution_rate',
            ])
            ->map(function (Employee $employee): array {
                $costs = $employee->getMonthlyPersonnelCosts();

                return [
                    'name' => $employee->name,
                    'department_name' => $employee->employeeDepartment?->name,
                    'total_raw' => (float) $costs['total'],
                    'gross' => Number::currency((float) $costs['gross']),
                    'employer_contributions' => Number::currency((float) $costs['employer_contributions']),
                    'total' => Number::currency((float) $costs['total']),
                ];
            })
            ->sortByDesc('total_raw')
            ->values()
            ->toArray();
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(Employee::class) => [
                Employee::class => ['created', 'updated', 'deleted'],
            ],
            $this->modelChannel(SalaryComponent::class) => [
                SalaryComponent::class => ['created', 'updated', 'deleted'],
            ],
        ];
    }
}
