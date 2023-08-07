<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateWorkTimeTypeRequest;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateWorkTimeType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateWorkTimeTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    public function performAction(): Model
    {
        $workTimeType = WorkTimeType::query()
            ->whereKey($this->data['id'])
            ->first();

        $workTimeType->fill($this->data);
        $workTimeType->save();

        return $workTimeType->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new WorkTimeType());

        $this->data = $validator->validate();
    }
}
