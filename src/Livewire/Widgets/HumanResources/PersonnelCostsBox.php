<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use FluxErp\Models\SalaryComponent;
use Illuminate\Support\Number;

class PersonnelCostsBox extends ValueBox
{
    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function calculateSum(): void
    {
        $costs = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->with([
                'salaryComponents:id,employee_id,amount,payment_interval,valid_from,valid_until',
                'workTimeModelHistory.workTimeModel:id,weekly_hours',
            ])
            ->get([
                'id',
                'salary_type',
                'base_salary',
                'hourly_rate',
                'employer_contribution_rate',
            ])
            ->map(fn (Employee $employee): array => $employee->getMonthlyPersonnelCosts());

        $sum = fn (string $key): float => (float) $costs->reduce(
            fn (string $carry, array $item): string => bcadd($carry, $item[$key]),
            '0'
        );

        $this->sum = Number::currency($sum('total'));
        $this->subValue = __(':gross gross + :contributions employer contributions per month', [
            'gross' => Number::currency($sum('gross')),
            'contributions' => Number::currency($sum('employer_contributions')),
        ]);
    }

    protected function icon(): string
    {
        return 'banknotes';
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
