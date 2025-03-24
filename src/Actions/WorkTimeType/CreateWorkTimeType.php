<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\CreateWorkTimeTypeRuleset;

class CreateWorkTimeType extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWorkTimeTypeRuleset::class;
    }

    public function performAction(): WorkTimeType
    {
        $workTimeType = app(WorkTimeType::class, ['attributes' => $this->data]);
        $workTimeType->save();

        return $workTimeType->fresh();
    }
}
