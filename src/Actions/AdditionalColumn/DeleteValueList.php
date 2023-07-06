<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeleteValueList implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => [
                'required',
                'integer',
                Rule::exists('additional_columns', 'id')->whereNotNull('values'),
            ],
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'warehouse.delete';
    }

    public static function description(): string|null
    {
        return 'delete warehouse';
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute()
    {
        return AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if (AdditionalColumn::query()
                ->whereKey($this->data['id'])
                ->first()
                ->modelValues()
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'model_has_values' => [__('Value list referenced by at least one model instance')],
            ])->errorBag('deleteValueList');
        }

        return $this;
    }
}
