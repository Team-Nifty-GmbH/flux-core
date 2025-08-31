<?php

namespace FluxErp\Actions\VacationBlackout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\VacationBlackout\UpdateVacationBlackoutRuleset;
use Illuminate\Support\Arr;

class UpdateVacationBlackout extends FluxAction
{
    public static function models(): array
    {
        return [VacationBlackout::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateVacationBlackoutRuleset::class;
    }

    public function performAction(): VacationBlackout
    {
        $vacationBlackout = resolve_static(VacationBlackout::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $employeeIds = Arr::pull($data, 'employee_ids');
        $employeeDepartmentIds = Arr::pull($data, 'employee_department_ids');
        $locationIds = Arr::pull($data, 'location_ids');

        $vacationBlackout->fill($data);
        $vacationBlackout->save();

        if (! is_null($employeeIds)) {
            $vacationBlackout->employees()->sync($employeeIds);
        }

        if (! is_null($employeeDepartmentIds)) {
            $vacationBlackout->employeeDepartments()->sync($employeeDepartmentIds);
        }

        if (! is_null($locationIds)) {
            $vacationBlackout->locations()->sync($locationIds);
        }

        return $vacationBlackout->fresh()->load(['employees', 'employeeDepartments', 'locations']);
    }
}
