<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;

class HeadcountBox extends ValueBox
{
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
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function calculateSum(): void
    {
        $now = now();

        $this->sum = Number::format(
            resolve_static(Employee::class, 'query')
                ->employed($now)
                ->count()
        );

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $newHires = resolve_static(Employee::class, 'query')
            ->whereBetween('employment_date', [$monthStart, $monthEnd])
            ->count();

        $departures = resolve_static(Employee::class, 'query')
            ->whereBetween('termination_date', [$monthStart, $monthEnd])
            ->count();

        $this->subValue = __(':hires / :departures this month', [
            'hires' => '+' . $newHires,
            'departures' => '-' . $departures,
        ]);
    }

    protected function icon(): string
    {
        return 'users';
    }
}
