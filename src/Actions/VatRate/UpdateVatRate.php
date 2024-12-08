<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\UpdateVatRateRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateVatRate extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateVatRateRuleset::class;
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): Model
    {
        $vatRate = resolve_static(VatRate::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $vatRate->fill($this->data);
        $vatRate->save();

        return $vatRate->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (($this->data['is_default'] ?? false)
            && ! resolve_static(VatRate::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
