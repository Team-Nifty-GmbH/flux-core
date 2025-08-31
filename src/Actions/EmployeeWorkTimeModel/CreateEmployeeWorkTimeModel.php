<?php

namespace FluxErp\Actions\EmployeeWorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rulesets\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModelRuleset;

class CreateEmployeeWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeWorkTimeModel::class];
    }

    public static function name(): string
    {
        return 'employee-work-time-model.create';
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeWorkTimeModelRuleset::class;
    }

    public function performAction(): EmployeeWorkTimeModel
    {
        // Close any existing open assignment for this employee
        if ($this->getData('employee_id')) {
            EmployeeWorkTimeModel::query()
                ->where('employee_id', $this->getData('employee_id'))
                ->whereNull('valid_until')
                ->update(['valid_until' => now()]);
        }

        $employeeWorkTimeModel = app(EmployeeWorkTimeModel::class, ['attributes' => $this->getData()]);
        $employeeWorkTimeModel->save();

        return $employeeWorkTimeModel->fresh();
    }
}
