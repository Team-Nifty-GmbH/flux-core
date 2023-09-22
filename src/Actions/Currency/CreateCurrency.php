<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Models\Currency;

class CreateCurrency extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCurrencyRequest())->rules();
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function performAction(): Currency
    {
        $this->data['is_default'] = ! Currency::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'];

        if ($this->data['is_default']) {
            Currency::query()->update(['is_default' => false]);
        }

        $currency = new Currency($this->data);
        $currency->save();

        return $currency->fresh();
    }
}
