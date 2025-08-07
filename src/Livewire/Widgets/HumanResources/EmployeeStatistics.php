<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\HrDashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\User;
use FluxErp\Traits\Widgetable;

class EmployeeStatistics extends ValueBox
{
    use Widgetable;

    public static function dashboardComponent(): array|string
    {
        return HrDashboard::class;
    }

    public function calculateSum(): void
    {
        $this->sum = resolve_static(User::class, 'query')
            ->whereNotNull('employee_number')
            ->where('is_active', true)
            ->count();

        $lastMonth = resolve_static(User::class, 'query')
            ->whereNotNull('employee_number')
            ->where('is_active', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($lastMonth > 0) {
            $this->growthRate = (($this->sum - $lastMonth) / $lastMonth) * 100;
        }

        $this->subValue = resolve_static(User::class, 'query')
            ->whereNotNull('employee_number')
            ->where('is_active', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() . ' ' . __('new this month');
    }

    protected function icon(): string
    {
        return 'users';
    }

    protected function title(): ?string
    {
        return __('Total Employees');
    }
}