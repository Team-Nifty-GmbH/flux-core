<?php

namespace FluxErp\Actions\WorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\WorkTimeModel\UpdateWorkTimeModelRuleset;

class UpdateWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWorkTimeModelRuleset::class;
    }

    public function performAction(): WorkTimeModel
    {
        $workTimeModel = resolve_static(WorkTimeModel::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $workTimeModel->fill($this->getData());
        $workTimeModel->save();

        return $workTimeModel->fresh();
    }
}