<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Currency;
use FluxErp\Rulesets\Currency\CreateCurrencyRuleset;

class CreateCurrency extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCurrencyRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Currency
    {
        $this->data['is_default'] = ! app(Currency::class)->query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            app(Currency::class)->query()->update(['is_default' => false]);
        }

        $currency = app(Currency::class, ['attributes' => $this->data]);
        $currency->save();

        return $currency->fresh();
    }
}
