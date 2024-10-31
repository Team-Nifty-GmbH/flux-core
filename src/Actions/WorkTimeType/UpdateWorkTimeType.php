<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\UpdateWorkTimeTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateWorkTimeType extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateWorkTimeTypeRuleset::class;
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function performAction(): Model
    {
        $workTimeType = resolve_static(WorkTimeType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $workTimeType->fill($this->data);
        $workTimeType->save();

        return $workTimeType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(WorkTimeType::class));

        $this->data = $validator->validate();
    }
}
