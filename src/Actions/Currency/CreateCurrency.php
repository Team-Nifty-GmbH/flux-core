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
        $currency = new Currency($this->data);
        $currency->save();

        return $currency->fresh();
    }
}
