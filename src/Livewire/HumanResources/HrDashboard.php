<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Facades\Widget;
use FluxErp\Livewire\Support\Dashboard;
use FluxErp\Livewire\Widgets\HumanResources\EmployeeStatistics;
use FluxErp\Livewire\Widgets\HumanResources\TodaysAbsences;
use FluxErp\Livewire\Widgets\HumanResources\UpcomingHolidays;
use FluxErp\Livewire\Widgets\HumanResources\VacationOverview;

class HrDashboard extends Dashboard
{
    protected bool $hasTimeSelector = false;

    public static function getDefaultWidgets(): array
    {
        return parent::mapDefaultWidgets(
            static::$defaultWidgets ??
            [
                Widget::get(EmployeeStatistics::class),
                Widget::get(VacationOverview::class),
                Widget::get(TodaysAbsences::class),
                Widget::get(UpcomingHolidays::class),
            ]
        );
    }
}