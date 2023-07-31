<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateWorkTimeTypeRequest;
use FluxErp\Models\WorkTimeType;
use Illuminate\Support\Facades\Validator;

class CreateWorkTimeType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateWorkTimeTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function execute(): WorkTimeType
    {
        $workTimeType = new WorkTimeType($this->data);
        $workTimeType->save();

        return $workTimeType->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new WorkTimeType());

        $this->data = $validator->validate();

        return $this;
    }
}
