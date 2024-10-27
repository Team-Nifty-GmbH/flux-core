<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Currency;
use FluxErp\Rulesets\Currency\UpdateCurrencyRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCurrency extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateCurrencyRuleset::class;
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Model
    {
        $currency = resolve_static(Currency::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['iso'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Currency::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
