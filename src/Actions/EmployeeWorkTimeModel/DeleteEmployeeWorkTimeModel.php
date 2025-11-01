<?php

namespace FluxErp\Actions\EmployeeWorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rulesets\EmployeeWorkTimeModel\DeleteEmployeeWorkTimeModelRuleset;

class DeleteEmployeeWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeWorkTimeModel::class];
    }

    public static function name(): string
    {
        return 'employee-work-time-model.delete';
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeWorkTimeModelRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
