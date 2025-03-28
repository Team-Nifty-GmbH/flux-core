<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\CreateValueListRuleset;
use Illuminate\Validation\ValidationException;

class CreateValueList extends FluxAction
{
    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateValueListRuleset::class;
    }

    public function performAction(): AdditionalColumn
    {
        $valueList = app(AdditionalColumn::class);
        $valueList->name = $this->data['name'];
        $valueList->model_type = $this->data['model_type'];
        $valueList->values = $this->data['values'];
        $valueList->save();

        return $valueList->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! array_is_list($this->data['values'])) {
            throw ValidationException::withMessages([
                'values' => ['Values array is no list'],
            ])->errorBag('createValueList');
        }

        if (resolve_static(AdditionalColumn::class, 'query')
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
