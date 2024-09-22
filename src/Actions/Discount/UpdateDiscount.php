<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;
use FluxErp\Rulesets\Discount\UpdateDiscountRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateDiscount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateDiscountRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Discount::class];
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

        // Check discount is max 100 if is_percentage = true
        if (($this->data['is_percentage'] ?? false)
            && $this->data['discount'] > 100
        ) {
            throw ValidationException::withMessages([
                'discount' => [__('validation.max', ['attribute' => 'discount', 'max' => 100])],
            ])->errorBag('updateDiscount');
        }
    }
}
