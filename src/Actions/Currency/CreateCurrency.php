<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Models\Currency;

class CreateCurrency extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCurrencyRequest())->rules();
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function execute(): Currency
    {
        $currency = new Currency($this->data);
        $currency->save();

        return $currency->fresh();
    }
}
