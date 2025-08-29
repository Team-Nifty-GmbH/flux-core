<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;

class VacationBalanceBox extends ValueBox
{
    #[Locked]
    public ?int $employeeId = null;

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
        return 0;
    }

    public function calculateSum(): void
    {
        /** @var Employee $employee */
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->first();

        $currentBalance = bcround($employee->getCurrentVacationDaysBalance(), 2);
        $baseVacation = bcround($employee->getTotalVacationDays(), 2);

        $this->sum = __(':days days available', ['days' => Number::format($currentBalance, 2)]);
        $this->subValue = __('of :total days', ['total' => Number::format($baseVacation, 2)]);
    }

    protected function icon(): string
    {
        return 'calendar';
    }
}
