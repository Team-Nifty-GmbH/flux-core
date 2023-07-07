<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeleteValueList extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                Rule::exists('additional_columns', 'id')->whereNotNull('values'),
            ],
        ];
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function execute(): bool|null
    {
        return AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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
