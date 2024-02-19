<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateValueListRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateValueList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(UpdateValueListRequest::class)->rules();
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): Model
    {
        $valueList = app(AdditionalColumn::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $valueList->fill($this->data);
        $valueList->save();

        return $valueList->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $valueList = app(AdditionalColumn::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['values'] ?? false) {
            if (! array_is_list($this->data['values'])) {
                $errors += [
                    'values' => [__('Values array is no list')],
                ];
            } elseif ($valueList->modelValues()->whereNotIn('meta.value', $this->data['values'])->exists()) {
                $errors += [
                    'values' => [__('Models with differing values exist')],
                ];
            }
        }

        $this->data['name'] = $this->data['name'] ?? $valueList->name;
        $this->data['model_type'] = $this->data['model_type'] ?? $valueList->model_type;

        if (app(AdditionalColumn::class)->query()
            ->where('id', '!=', $this->data['id'])
            ->where('name', $this->data['name'])
            ->where('model_type', $this->data['model_type'])
            ->exists()
        ) {
            $errors += [
                'name_model' => [__('Name model combination already exists')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateValueList');
        }
    }
}
