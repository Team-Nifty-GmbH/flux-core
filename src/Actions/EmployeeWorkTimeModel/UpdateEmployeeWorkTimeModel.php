<?php

namespace FluxErp\Actions\EmployeeWorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rulesets\EmployeeWorkTimeModel\UpdateEmployeeWorkTimeModelRuleset;

class UpdateEmployeeWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeWorkTimeModel::class];
    }

    public static function name(): string
    {
        return 'employee-work-time-model.update';
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeWorkTimeModelRuleset::class;
    }

    public function performAction(): EmployeeWorkTimeModel
    {
        $employeeWorkTimeModel = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();

        $employeeWorkTimeModel->fill($this->getData());
        $employeeWorkTimeModel->save();

        return $employeeWorkTimeModel->fresh();
    }
}
