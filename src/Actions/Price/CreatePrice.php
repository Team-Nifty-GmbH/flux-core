<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePriceRequest;
use FluxErp\Models\Price;

class CreatePrice extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreatePriceRequest())->rules();
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): Price
    {
        $price = new Price($this->data);
        $price->save();

        return $price->fresh();
    }
}
