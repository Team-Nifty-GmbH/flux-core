<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;

class CurrentWorkTimeModel extends ValueBox
{
    #[Locked]
    public ?int $employeeId = null;

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
        /** @var WorkTimeModel $employee */
        $workTimeModel = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->first()
            ->getWorkTimeModel();

        $this->sum = Number::format($workTimeModel->getDailyWorkHours(), 2) . 'h';
        $this->subValue = __(':days days per week', ['days' => $workTimeModel->work_days_per_week]);
    }

    protected function icon(): string
    {
        return 'wrench';
    }
}
