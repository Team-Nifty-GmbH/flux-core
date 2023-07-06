<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateValueListRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateValueList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateValueListRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'value-list.update';
    }

    public static function description(): string|null
    {
        return 'update value list';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): Model
    {
        $valueList = AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first();

        $valueList->fill($this->data);
        $valueList->save();

        return $valueList->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $errors = [];
        $valueList = AdditionalColumn::query()
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

        if (AdditionalColumn::query()
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

        return $this;
    }
}
