<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateValueListRequest;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateValueList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateValueListRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'value-list.create';
    }

    public static function description(): string|null
    {
        return 'create value list';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): AdditionalColumn
    {
        $valueList = new AdditionalColumn();
        $valueList->name = $this->data['name'];
        $valueList->model_type = $this->data['model_type'];
        $valueList->values = $this->data['values'];
        $valueList->save();

        return $valueList;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if (! array_is_list($this->data['values'])) {
            throw ValidationException::withMessages([
                'values' => ['Values array is no list']
            ])->errorBag('createValueList');
        }

        if (AdditionalColumn::query()
            ->where('name', $this->data['name'])
            ->where('model_type', $this->data['model_type'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name_model' => [__('Name model combination already exists')]
            ])->errorBag('createValueList');
        }

        return $this;
    }
}
