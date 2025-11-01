<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;

class OvertimeBalanceBox extends ValueBox
{
    #[Locked]
    public ?int $employeeId = null;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 1;
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

        $this->sum = Number::format($employee->getCurrentOvertimeBalance(), 0) . 'h';
        $this->previousSum = null;
        $this->growthRate = null;

        $this->shouldBePositive = true;
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
