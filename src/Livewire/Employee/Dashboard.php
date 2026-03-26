<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Facades\Widget;
use FluxErp\Livewire\Support\Dashboard as BaseDashboard;
use FluxErp\Livewire\Widgets\Employee\AttendanceOverview;
use FluxErp\Livewire\Widgets\Employee\CurrentWorkTimeModel;
use FluxErp\Livewire\Widgets\Employee\OvertimeBalanceBox;
use FluxErp\Livewire\Widgets\Employee\VacationBalanceBox;
use FluxErp\Livewire\Widgets\Employee\VacationYearlyOverview;
use FluxErp\Livewire\Widgets\Employee\WorkTimeOverview;
use Livewire\Attributes\Modelable;

class Dashboard extends BaseDashboard
{
    #[Modelable]
    public ?int $employeeId = null;

    public array $params = [
        'timeFrame' => TimeFrameEnum::ThisYear,
        'start' => null,
        'end' => null,
    ];

    public static function getDefaultWidgets(): array
    {
        return static::mapDefaultWidgets(
            static::$defaultWidgets ??
            [
                Widget::get(VacationBalanceBox::class),
                Widget::get(OvertimeBalanceBox::class),
                Widget::get(CurrentWorkTimeModel::class),
                Widget::get(AttendanceOverview::class),
                Widget::get(WorkTimeOverview::class),
                Widget::get(VacationYearlyOverview::class),
            ]
        );
    }

    public function getWidgetAttributes(): array
    {
        return [
            'employeeId' => $this->employeeId,
        ];
    }
}
