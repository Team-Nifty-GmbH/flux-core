<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;
use FluxErp\Rulesets\Discount\CreateDiscountRuleset;
use Illuminate\Validation\ValidationException;

class CreateDiscount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateDiscountRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function performAction(): Discount
    {
        $discount = app(Discount::class, ['attributes' => $this->data]);
        $discount->save();

        return $discount->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Check discount is max 1 if is_percentage = true
        if ($this->data['is_percentage']
            && $this->data['discount'] > 1
        ) {
            throw ValidationException::withMessages([
                'discount' => [__('validation.max', ['attribute' => 'discount', 'max' => 1])],
            ])->errorBag('createDiscount');
        }
    }
}
