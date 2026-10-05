<?php

namespace FluxErp\Livewire\Widgets\Employee;

use FluxErp\Contracts\HasApiResponse;
use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Traits\Livewire\Widget\RespondsToApiRequests;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;

class CurrentWorkTimeModel extends ValueBox implements HasApiResponse
{
    use RespondsToApiRequests;

    #[Locked]
    public ?int $employeeId = null;

    public ?float $dailyTarget = null;

    public ?float $weeklyTarget = null;

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

        $dailyWorkHours = $workTimeModel->getDailyWorkHours();

        $this->sum = Number::format($dailyWorkHours, 2) . 'h';
        $this->subValue = $workTimeModel->has_fixed_hours
            ? __(':days days per week', ['days' => $workTimeModel->work_days_per_week])
            : __('No fixed hours');
        $this->dailyTarget = (float) $dailyWorkHours;
        $this->weeklyTarget = $workTimeModel->has_fixed_hours ? (float) $workTimeModel->weekly_hours : 0.0;
    }

    protected function apiResponseProperties(): array
    {
        return [
            'sum',
            'subValue',
            'dailyTarget',
            'weeklyTarget',
        ];
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

    protected function icon(): string
    {
        return 'wrench';
    }
}
