<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class UpcomingBirthdaysWidget extends Component
{
    use Widgetable;

    #[Locked]
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
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.upcoming-birthdays');
    }

    public function loadData(): void
    {
        $today = today();
        $endDate = $today->copy()->addDays(30);

        $startMonthDay = $today->format('md');
        $endMonthDay = $endDate->format('md');

        $this->birthdays = resolve_static(Employee::class, 'query')
            ->employed($today)
            ->whereNotNull('date_of_birth')
            ->where(function (Builder $query) use ($startMonthDay, $endMonthDay): void {
                if ($startMonthDay <= $endMonthDay) {
                    $query->whereRaw(
                        "REPLACE(SUBSTR(date_of_birth, 6), '-', '') BETWEEN ? AND ?",
                        [$startMonthDay, $endMonthDay]
                    );
                } else {
                    $query
                        ->whereRaw("REPLACE(SUBSTR(date_of_birth, 6), '-', '') >= ?", [$startMonthDay])
                        ->orWhereRaw("REPLACE(SUBSTR(date_of_birth, 6), '-', '') <= ?", [$endMonthDay]);
                }
            })
            ->get(['id', 'name', 'date_of_birth'])
            ->map(function (Employee $employee) use ($today): array {
                $birthday = $employee->date_of_birth->copy()->year($today->year);

                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }

                return [
                    'name' => $employee->name,
                    'date' => $birthday->locale(app()->getLocale())->isoFormat('D. MMM'),
                    'date_sort' => $birthday->toDateString(),
                    'age' => $employee->date_of_birth->diffInYears($birthday),
                ];
            })
            ->sortBy('date_sort')
            ->toArray();
    }
}
