<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Models\Discount;

class CreateDiscount extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateDiscountRequest())->rules();
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function performAction(): Discount
    {
        $discount = new Discount($this->data);
        $discount->save();

        return $discount;
    }
}
