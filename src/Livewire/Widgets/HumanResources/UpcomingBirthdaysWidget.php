<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpcomingBirthdaysWidget extends Component
{
    use Widgetable;

    public array $birthdays = [];

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
        return 2;
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
        $this->loadBirthdays();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.upcoming-birthdays');
    }

    public function loadBirthdays(): void
    {
        $today = now()->startOfDay();
        $endDate = $today->copy()->addDays(30);

        $this->birthdays = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->whereNotNull('date_of_birth')
            ->get(['id', 'name', 'date_of_birth'])
            ->map(function (Employee $employee) use ($today, $endDate) {
                $birthday = $employee->date_of_birth->copy()->year($today->year);

                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }

                if ($birthday->gt($endDate)) {
                    return null;
                }

                $age = $employee->date_of_birth->diffInYears($birthday);

                return [
                    'name' => $employee->name,
                    'date' => $birthday->locale(app()->getLocale())->isoFormat('D. MMM'),
                    'date_sort' => $birthday->toDateString(),
                    'age' => $age,
                ];
            })
            ->filter()
            ->sortBy('date_sort')
            ->take(10)
            ->values()
            ->toArray();
    }
}
