<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;

class VacationBalanceTotalBox extends ValueBox
{
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
        return 3;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function calculateSum(): void
    {
        $employees = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->select(['id', 'employment_date', 'termination_date', 'is_active', 'vacation_carryover_rule_id'])
            ->get();

        $totalBalance = '0';
        foreach ($employees as $employee) {
            $totalBalance = bcadd($totalBalance, $employee->getCurrentVacationDaysBalance());
        }

        $employeeCount = $employees->count();

        $this->sum = Number::format(bcround($totalBalance, 1), 1)
            . ' ' . __('days');

        $average = $employeeCount > 0
            ? bcround(bcdiv($totalBalance, $employeeCount, 4), 1)
            : '0.0';

        $this->subValue = __('Ø :days days per employee', [
            'days' => Number::format($average, 1),
        ]);
    }

    protected function icon(): string
    {
        return 'sun';
    }
}
