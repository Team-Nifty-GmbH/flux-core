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
            ->firstOrFail();

        $data = $this->getData();
        $employees = Arr::pull($data, 'employees');
        $employeeDepartments = Arr::pull($data, 'employee_departments');
        $locations = Arr::pull($data, 'locations');

        $vacationBlackout->fill($data);
        $vacationBlackout->save();

        if (! is_null($employees)) {
            $vacationBlackout->employees()->sync($employees);
        }

        if (! is_null($employeeDepartments)) {
            $vacationBlackout->employeeDepartments()->sync($employeeDepartments);
        }

        if (! is_null($locations)) {
            $vacationBlackout->locations()->sync($locations);
        }

        return $vacationBlackout->withoutRelations()->fresh();
    }
}
