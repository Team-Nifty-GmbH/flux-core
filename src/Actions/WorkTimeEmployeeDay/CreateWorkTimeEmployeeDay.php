<?php

namespace FluxErp\Actions\WorkTimeEmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Rulesets\WorkTimeEmployeeDay\CreateWorkTimeEmployeeDayRuleset;

class CreateWorkTimeEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeEmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWorkTimeEmployeeDayRuleset::class;
    }

    public function performAction(): WorkTimeEmployeeDay
    {
        $workTimeEmployeeDay = app(WorkTimeEmployeeDay::class, ['attributes' => $this->getData()]);
        $workTimeEmployeeDay->save();

        return $workTimeEmployeeDay->fresh();
    }
}
