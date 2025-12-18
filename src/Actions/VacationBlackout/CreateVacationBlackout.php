<?php

namespace FluxErp\Actions\VacationBlackout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\VacationBlackout\CreateVacationBlackoutRuleset;
use Illuminate\Support\Arr;

class CreateVacationBlackout extends FluxAction
{
    public static function models(): array
    {
        return [VacationBlackout::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateVacationBlackoutRuleset::class;
    }

    public function performAction(): VacationBlackout
    {
        $data = $this->getData();
        $employees = Arr::pull($data, 'employees');
        $employeeDepartments = Arr::pull($data, 'employee_departments');
        $locations = Arr::pull($data, 'locations');

        $vacationBlackout = app(VacationBlackout::class, ['attributes' => $data]);
        $vacationBlackout->save();

        if ($employees) {
            $vacationBlackout->employees()->attach($employees);
        }

        if ($employeeDepartments) {
            $vacationBlackout->employeeDepartments()->attach($employeeDepartments);
        }

        if ($locations) {
            $vacationBlackout->locations()->attach($locations);
        }

        return $vacationBlackout->refresh();
    }
}
