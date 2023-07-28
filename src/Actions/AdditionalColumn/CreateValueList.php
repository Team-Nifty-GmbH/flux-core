<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateValueListRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Validation\ValidationException;

class CreateValueList extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateValueListRequest())->rules();
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): AdditionalColumn
    {
        $valueList = new AdditionalColumn();
        $valueList->name = $this->data['name'];
        $valueList->model_type = $this->data['model_type'];
        $valueList->values = $this->data['values'];
        $valueList->save();

        return $valueList;
    }

    public function validateData(): void
    {
        parent::validateData();

        if (! array_is_list($this->data['values'])) {
            throw ValidationException::withMessages([
                'values' => ['Values array is no list'],
            ])->errorBag('createValueList');
        }

        if (AdditionalColumn::query()
            ->where('name', $this->data['name'])
            ->where('model_type', $this->data['model_type'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name_model' => [__('Name model combination already exists')],
            ])->errorBag('createValueList');
        }
    }
}
