<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;

class OvertimeTotalBox extends ValueBox
{
    public bool $shouldBePositive = false;

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
        return 2;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function calculateSum(): void
    {
        $employees = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->select(['id', 'employment_date', 'termination_date', 'is_active'])
            ->get();

        $totalOvertime = '0';
        foreach ($employees as $employee) {
            $totalOvertime = bcadd($totalOvertime, $employee->getCurrentOvertimeBalance());
        }

        $employeeCount = $employees->count();
        $this->sum = Number::format(bcround($totalOvertime, 2), 2) . 'h';

        $average = $employeeCount > 0
            ? bcround(bcdiv($totalOvertime, $employeeCount, 4), 2)
            : '0.00';

        $this->subValue = __('Ø :hours h per employee', [
            'hours' => Number::format($average, 2),
        ]);
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
