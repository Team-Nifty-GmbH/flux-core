<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;
use FluxErp\Rulesets\Discount\UpdateDiscountRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateDiscount extends FluxAction
{
    public static function models(): array
    {
        return [Discount::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateDiscountRuleset::class;
    }

    public function performAction(): Model
    {
        $discount = resolve_static(Discount::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $discount->fill($this->data);
        $discount->save();

        return $discount->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Check discount is max 1 if is_percentage = true
        if (($this->data['is_percentage'] ?? false)
            && $this->data['discount'] > 1
        ) {
            throw ValidationException::withMessages([
                'discount' => [__('validation.max', ['attribute' => 'discount', 'max' => 1])],
            ])->errorBag('updateDiscount');
        }
    }
}
