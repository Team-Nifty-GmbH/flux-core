<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TopOvertimeWidget extends Component
{
    use Widgetable;

    #[Locked]
    public array $employees = [];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 4;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.top-overtime');
    }

    public function loadData(): void
    {
        $this->employees = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->with('employeeDepartment:id,name')
            ->get()
            ->map(function (Employee $employee) {
                $balance = $employee->getCurrentOvertimeBalance();

                return [
                    'name' => $employee->name,
                    'department_name' => $employee->employeeDepartment?->name,
                    'overtime_raw' => (float) $balance,
                    'overtime_hours' => Number::format($balance, 2) . 'h',
                ];
            })
            ->sortByDesc('overtime_raw')
            ->take(10)
            ->values()
            ->map(fn (array $item, int $index) => array_merge($item, ['rank' => $index + 1]))
            ->toArray();
    }
}
