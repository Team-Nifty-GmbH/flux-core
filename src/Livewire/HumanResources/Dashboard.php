<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Facades\Widget;
use FluxErp\Livewire\Support\Dashboard as BaseDashboard;
use FluxErp\Livewire\Widgets\HumanResources\AbsencesTodayBox;
use FluxErp\Livewire\Widgets\HumanResources\AbsenceTypeDistributionChart;
use FluxErp\Livewire\Widgets\HumanResources\DepartmentHeadcountChart;
use FluxErp\Livewire\Widgets\HumanResources\HeadcountBox;
use FluxErp\Livewire\Widgets\HumanResources\NewHiresBox;
use FluxErp\Livewire\Widgets\HumanResources\OvertimeTotalBox;
use FluxErp\Livewire\Widgets\HumanResources\OvertimeTrendChart;
use FluxErp\Livewire\Widgets\HumanResources\SickDaysTrendChart;
use FluxErp\Livewire\Widgets\HumanResources\SickRateBox;
use FluxErp\Livewire\Widgets\HumanResources\VacationBalanceTotalBox;
use FluxErp\Livewire\Widgets\HumanResources\WorkTimeComparisonChart;
use FluxErp\Livewire\Widgets\TeamAbsenceCalendar;

class Dashboard extends BaseDashboard
{
    protected bool $hasTimeSelector = true;

    public static function getDefaultWidgets(): array
    {
        return parent::mapDefaultWidgets(
            static::$defaultWidgets ?? [
                Widget::get(HeadcountBox::class),
                Widget::get(AbsencesTodayBox::class),
                Widget::get(OvertimeTotalBox::class),
                Widget::get(VacationBalanceTotalBox::class),
                Widget::get(SickRateBox::class),
                Widget::get(NewHiresBox::class),
                Widget::get(TeamAbsenceCalendar::class),
                Widget::get(WorkTimeComparisonChart::class),
                Widget::get(AbsenceTypeDistributionChart::class),
                Widget::get(SickDaysTrendChart::class),
                Widget::get(DepartmentHeadcountChart::class),
                Widget::get(OvertimeTrendChart::class),
            ]
        );
    }
}
