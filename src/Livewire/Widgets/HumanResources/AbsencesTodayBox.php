<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;

class AbsencesTodayBox extends ValueBox
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
        return 1;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function calculateSum(): void
    {
        $today = now()->toDateString();

        $absentCount = resolve_static(AbsenceRequest::class, 'query')
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->distinct('employee_id')
            ->count('employee_id');

        $totalActive = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->count();

        $this->sum = Number::format($absentCount);

        $presentRate = $totalActive > 0
            ? bcround(bcmul(bcdiv(bcsub($totalActive, $absentCount), $totalActive, 4), '100', 2), 0)
            : 100;

        $this->subValue = __(':rate% present', ['rate' => $presentRate]);
    }

    protected function icon(): string
    {
        return 'user-minus';
    }
}
