<?php

namespace FluxErp\Console\Commands\HumanResources;

use FluxErp\Actions\EmployeeDay\CloseEmployeeDay as CloseEmployeeDayAction;
use FluxErp\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

class CloseEmployeeDay extends Command
{
    protected $signature = 'flux:close-employee-day {date?} {employee?*}';

    protected $description = 'Close Employee Day for given date';

    public function handle(): int
    {
        $date = Carbon::parse($this->argument('date') ?? Carbon::yesterday())->toDateString();
        $employeeInput = $this->argument('employee');

        $employees = resolve_static(Employee::class, 'query')
            ->when($employeeInput, fn (Builder $query) => $query->whereKey($employeeInput))
            ->where('is_active', true)
            ->get(['id']);

        foreach ($employees as $employee) {
            try {
                CloseEmployeeDayAction::make([
                    'employee_id' => $employee->getKey(),
                    'date' => $date,
                ])
                    ->validate()
                    ->execute();
            } catch (Throwable) {
                continue;
            }
        }

        return 0;
    }
}
