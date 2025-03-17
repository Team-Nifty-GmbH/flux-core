<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\PriceList\CreatePriceListRuleset;
use Illuminate\Validation\ValidationException;

class CreatePriceList extends FluxAction
{
    public static function models(): array
    {
        return [PriceList::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePriceListRuleset::class;
    }

    public function performAction(): PriceList
    {
        $priceList = app(PriceList::class, ['attributes' => $this->data]);
        $priceList->save();

        // Create Discount
        if (($discount = ($this->data['discount'] ?? false)) && $this->data['discount']['discount'] != 0) {
            CreateDiscount::make(
                array_merge(
                    $discount,
                    [
                        'model_type' => app(PriceList::class)->getMorphClass(),
                        'model_id' => $priceList->id,
                    ]
                )
            )->execute();
        }

        return $priceList->fresh($discount ? ['discount'] : []);
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Check discount is max 1 if is_percentage = true
        if (($this->data['discount'] ?? false)
            && $this->data['discount']['is_percentage']
            && $this->data['discount']['discount'] > 1
        ) {
            throw ValidationException::withMessages([
                'discount.discount' => [__('validation.max', ['attribute' => 'discount', 'max' => 1])],
            ])->errorBag('createPriceList');
        }
    }
}
