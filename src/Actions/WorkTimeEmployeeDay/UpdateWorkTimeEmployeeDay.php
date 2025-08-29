<?php

namespace FluxErp\Actions\WorkTimeEmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Rulesets\WorkTimeEmployeeDay\UpdateWorkTimeEmployeeDayRuleset;

class UpdateWorkTimeEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeEmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWorkTimeEmployeeDayRuleset::class;
    }

    public function performAction(): WorkTimeEmployeeDay
    {
        $workTimeEmployeeDay = resolve_static(WorkTimeEmployeeDay::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();

        $workTimeEmployeeDay->fill($this->getData());
        $workTimeEmployeeDay->save();

        return $workTimeEmployeeDay->fresh();
    }
}
