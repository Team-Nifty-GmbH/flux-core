<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Models\Discount;

class CreateDiscount extends FluxAction
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

        return $discount->fresh();
    }
}
