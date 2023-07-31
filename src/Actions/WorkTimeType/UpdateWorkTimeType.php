<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateWorkTimeTypeRequest;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateWorkTimeType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateWorkTimeTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function execute(): Model
    {
        $workTimeType = WorkTimeType::query()
            ->whereKey($this->data['id'])
            ->first();

        $workTimeType->fill($this->data);
        $workTimeType->save();

        return $workTimeType->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new WorkTimeType());

        $this->data = $validator->validate();

        return $this;
    }
}
