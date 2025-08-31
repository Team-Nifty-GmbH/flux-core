<?php

namespace FluxErp\Actions\WorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\WorkTimeModel\CreateWorkTimeModelRuleset;

class CreateWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWorkTimeModelRuleset::class;
    }

    public function performAction(): WorkTimeModel
    {
        $workTimeModel = app(WorkTimeModel::class, ['attributes' => $this->getData()]);
        $workTimeModel->save();

        return $workTimeModel->fresh();
    }
}
