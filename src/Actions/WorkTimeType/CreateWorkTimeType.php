<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\CreateWorkTimeTypeRuleset;
use Illuminate\Support\Facades\Validator;

class CreateWorkTimeType extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateWorkTimeTypeRuleset::class;
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function performAction(): WorkTimeType
    {
        $workTimeType = app(WorkTimeType::class, ['attributes' => $this->data]);
        $workTimeType->save();

        return $workTimeType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(WorkTimeType::class));

        $this->data = $validator->validate();
    }
}
