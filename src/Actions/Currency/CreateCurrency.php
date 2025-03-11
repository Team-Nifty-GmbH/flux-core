<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Currency;
use FluxErp\Rulesets\Currency\CreateCurrencyRuleset;

class CreateCurrency extends FluxAction
{
    public static function models(): array
    {
        return [Currency::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCurrencyRuleset::class;
    }

    public function performAction(): Currency
    {
        $currency = app(Currency::class, ['attributes' => $this->data]);
        $currency->save();

        return $currency->fresh();
    }
}
