<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Models\Discount;

class CreateDiscount extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateDiscountRequest())->rules();
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function execute(): Discount
    {
        $discount = new Discount($this->data);
        $discount->save();

        return $discount->fresh();
    }
}
