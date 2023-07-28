<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateDiscountRequest;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Model;

class UpdateDiscount extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateDiscountRequest())->rules();
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function performAction(): Model
    {
        $discount = Discount::query()
            ->whereKey($this->data['id'])
            ->first();

        $discount->fill($this->data);
        $discount->save();

        return $discount->withoutRelations()->fresh();
    }
}
