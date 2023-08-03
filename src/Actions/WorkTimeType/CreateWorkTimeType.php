<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateWorkTimeTypeRequest;
use FluxErp\Models\WorkTimeType;
use Illuminate\Support\Facades\Validator;

class CreateWorkTimeType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateWorkTimeTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function performAction(): WorkTimeType
    {
        $workTimeType = new WorkTimeType($this->data);
        $workTimeType->save();

        return $workTimeType->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new WorkTimeType());

        $this->data = $validator->validate();
    }
}
