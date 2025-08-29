<?php

namespace FluxErp\Actions\WorkTimeEmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Rulesets\WorkTimeEmployeeDay\DeleteWorkTimeEmployeeDayRuleset;

class DeleteWorkTimeEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeEmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWorkTimeEmployeeDayRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(WorkTimeEmployeeDay::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ?->delete();
    }
}
