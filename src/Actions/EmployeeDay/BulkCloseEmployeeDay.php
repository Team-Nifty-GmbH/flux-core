<?php

namespace FluxErp\Actions\EmployeeDay;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rulesets\EmployeeDay\BulkCloseEmployeeDayRuleset;
use Illuminate\Validation\ValidationException;

class BulkCloseEmployeeDay extends DispatchableFluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return BulkCloseEmployeeDayRuleset::class;
    }

    public function performAction(): array
    {
        $period = array_filter($this->getData('timeframe'));
        if (count($period) > 1) {
            $period = new DatePeriod(
                Carbon::parse($period[0]),
                DateInterval::createFromDateString('1 day'),
                Carbon::parse($period[1]),
                DatePeriod::INCLUDE_END_DATE
            );
        }

        $employeeDays = [];
        foreach ($this->getData('employees') as $employee) {
            foreach ($period as $date) {
                try {
                    $employeeDays[] = CloseEmployeeDay::make([
                        'employee_id' => $employee,
                        'date' => $date,
                    ])
                        ->validate()
                        ->execute();
                } catch (ValidationException $e) {
                    report($e);
                }
            }
        }

        return $employeeDays;
    }
}
