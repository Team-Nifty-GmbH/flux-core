<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\DeleteValueListRuleset;
use Illuminate\Validation\ValidationException;

class DeleteValueList extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteValueListRuleset::class;
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(AdditionalColumn::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(AdditionalColumn::class, 'query')
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
