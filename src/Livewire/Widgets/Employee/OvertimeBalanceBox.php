<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Contracts\HasApiResponse;
use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use FluxErp\Rules\ModelExists;
use FluxErp\Traits\Livewire\Widget\RespondsToApiRequests;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;

class OvertimeBalanceBox extends ValueBox implements HasApiResponse
{
    use RespondsToApiRequests;

    #[Locked]
    public ?int $employeeId = null;

    public ?float $overtimeHours = null;

    public static function getCategory(): ?string
    {
        return 'Employees';
    }

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

        $overtimeBalance = $employee->getCurrentOvertimeBalance();

        $this->sum = Number::format($overtimeBalance, 2) . 'h';
        $this->overtimeHours = (float) $overtimeBalance;
        $this->previousSum = null;
        $this->growthRate = null;

        $this->shouldBePositive = true;
    }

    protected function apiRules(): array
    {
        return [
            'employeeId' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
        ];
    }

    protected function apiResponseProperties(): array
    {
        return [
            'sum',
            'overtimeHours',
        ];
    }

    protected function icon(): string
    {
        return 'clock';
    }
}
