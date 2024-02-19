<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeleteValueList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                (new ModelExists(AdditionalColumn::class))->whereNotNull('values'),
            ],
        ];
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): ?bool
    {
        return app(AdditionalColumn::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (app(AdditionalColumn::class)->query()
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
