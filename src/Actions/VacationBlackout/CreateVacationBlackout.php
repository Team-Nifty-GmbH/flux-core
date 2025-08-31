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
        $employeeIds = Arr::pull($data, 'employee_ids', []);
        $employeeDepartmentIds = Arr::pull($data, 'employee_department_ids', []);
        $locationIds = Arr::pull($data, 'location_ids', []);

        $vacationBlackout = app(VacationBlackout::class, ['attributes' => $data]);
        $vacationBlackout->save();

        if ($employeeIds) {
            $vacationBlackout->employees()->sync($employeeIds);
        }

        if ($employeeDepartmentIds) {
            $vacationBlackout->employeeDepartments()->sync($employeeDepartmentIds);
        }

        if ($locationIds) {
            $vacationBlackout->locations()->sync($locationIds);
        }

        return $vacationBlackout->fresh()->load(['employees', 'employeeDepartments', 'locations']);
    }
}
