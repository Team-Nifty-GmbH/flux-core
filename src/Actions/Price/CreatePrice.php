<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePriceRequest;
use FluxErp\Models\Price;

class CreatePrice extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePriceRequest())->rules();
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function performAction(): Price
    {
        $price = new Price($this->data);
        $price->save();

        return $price;
    }
}
