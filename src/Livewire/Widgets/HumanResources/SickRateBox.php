<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\EmployeeDay;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Support\Number;

class SickRateBox extends ValueBox
{
    use IsTimeFrameAwareWidget;

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
        return 4;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateSum();
    }

    public function calculateSum(): void
    {
        $start = $this->getStart();
        $end = $this->getEnd();

        $currentQuery = resolve_static(EmployeeDay::class, 'query')
            ->where('is_work_day', true)
            ->whereBetween('date', [$start, $end]);

        $totalWorkDays = $currentQuery->clone()->count();
        $sickDaysSum = $currentQuery->clone()->sum('sick_days_used');

        $sickRate = $totalWorkDays > 0
            ? bcround(bcmul(bcdiv($sickDaysSum, $totalWorkDays, 6), '100', 4), 1)
            : '0.0';

        $this->sum = Number::format($sickRate, 1) . '%';
        $this->subValue = __(':days sick days total', [
            'days' => Number::format(bcround($sickDaysSum, 1), 1),
        ]);

        $previousStart = $this->getStartPrevious();
        $previousEnd = $this->getEndPrevious();

        $previousQuery = resolve_static(EmployeeDay::class, 'query')
            ->where('is_work_day', true)
            ->whereBetween('date', [$previousStart, $previousEnd]);

        $previousTotalWorkDays = $previousQuery->clone()->count();
        $previousSickDaysSum = $previousQuery->clone()->sum('sick_days_used');

        $previousRate = $previousTotalWorkDays > 0
            ? bcround(bcmul(bcdiv($previousSickDaysSum, $previousTotalWorkDays, 6), '100', 4), 1)
            : '0.0';

        $this->previousSum = Number::format($previousRate, 1) . '%';

        $this->growthRate = bccomp($previousRate, '0', 4) !== 0
            ? bcround(bcmul(bcdiv(bcsub($sickRate, $previousRate), $previousRate, 6), '100', 4), 1)
            : null;
    }

    protected function icon(): string
    {
        return 'heart';
    }
}
