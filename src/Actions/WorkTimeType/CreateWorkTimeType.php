<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\CreateWorkTimeTypeRuleset;
use Illuminate\Support\Facades\Validator;

class CreateWorkTimeType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateWorkTimeTypeRuleset::class, 'getRules');
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
