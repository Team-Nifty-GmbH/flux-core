<?php

namespace FluxErp\Actions\AdditionalColumn;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rulesets\AdditionalColumn\UpdateAdditionalColumnRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateAdditionalColumn extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateAdditionalColumnRuleset::class;
    }

    public static function models(): array
    {
        return [AdditionalColumn::class];
    }

    public function performAction(): Model
    {
        $additionalColumn = resolve_static(AdditionalColumn::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $additionalColumn->fill($this->data);
        $additionalColumn->save();

        return $additionalColumn->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $additionalColumn = resolve_static(AdditionalColumn::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if (! is_null($additionalColumn->values)
            && ! is_null($this->getData('values'))
            && $additionalColumn->modelValues()->whereNotIn('meta.value', $this->data['values'])->exists()
        ) {
            throw ValidationException::withMessages([
                'values' => [__('Models with differing values exist')],
            ]);
        }
    }
}
