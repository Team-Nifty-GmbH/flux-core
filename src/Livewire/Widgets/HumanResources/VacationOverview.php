<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\HrDashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\User;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Traits\Widgetable;

class VacationOverview extends ValueBox
{
    use Widgetable;

    public static function dashboardComponent(): array|string
    {
        return HrDashboard::class;
    }

    public function calculateSum(): void
    {
        $users = resolve_static(User::class, 'query')
            ->whereNotNull('employee_number')
            ->where('is_active', true)
            ->get();
        
        $totalVacationDays = $users->sum('vacation_days_current');
        $usedVacationDays = resolve_static(AbsenceRequest::class, 'query')
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('days_requested');
        
        $this->sum = $totalVacationDays - $usedVacationDays;
        
        $pendingRequests = resolve_static(AbsenceRequest::class, 'query')
            ->where('status', 'pending')
            ->count();
        
        if ($pendingRequests > 0) {
            $this->subValue = $pendingRequests . ' ' . __('pending requests');
        } else {
            $this->subValue = __('No pending requests');
        }
        
        if ($totalVacationDays > 0) {
            $this->growthRate = -(($usedVacationDays / $totalVacationDays) * 100);
        }
    }

    protected function icon(): string
    {
        return 'calendar';
    }

    protected function title(): ?string
    {
        return __('Vacation Days Remaining');
    }
}