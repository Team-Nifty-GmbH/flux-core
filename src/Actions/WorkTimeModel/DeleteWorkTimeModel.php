<?php

namespace FluxErp\Actions\WorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\WorkTimeModel\DeleteWorkTimeModelRuleset;

class DeleteWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteWorkTimeModelRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(WorkTimeModel::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
