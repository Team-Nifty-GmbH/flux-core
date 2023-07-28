<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\AdditionalColumn;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeleteValueList extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
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

    public function performAction(): ?bool
    {
        return AdditionalColumn::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
