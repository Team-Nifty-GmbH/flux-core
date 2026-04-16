<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Support\Number;

class NewHiresBox extends ValueBox
{
    use IsTimeFrameAwareWidget;

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
        return 5;
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

        $newHires = resolve_static(Employee::class, 'query')
            ->whereBetween('employment_date', [$start, $end])
            ->count();

        $departures = resolve_static(Employee::class, 'query')
            ->whereBetween('termination_date', [$start, $end])
            ->count();

        $this->sum = Number::format($newHires);
        $this->subValue = __(':count departures', ['count' => $departures]);

        $previousStart = $this->getStartPrevious();
        $previousEnd = $this->getEndPrevious();

        $previousHires = resolve_static(Employee::class, 'query')
            ->whereBetween('employment_date', [$previousStart, $previousEnd])
            ->count();

        $this->previousSum = Number::format($previousHires);

        $this->growthRate = $previousHires > 0
            ? bcround(bcmul(bcdiv(bcsub($newHires, $previousHires), $previousHires, 6), '100', 4), 1)
            : null;
    }

    protected function icon(): string
    {
        return 'user-plus';
    }
}
