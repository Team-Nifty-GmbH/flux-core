<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Models\PriceList;

class CreatePriceList extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePriceListRequest())->rules();
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function performAction(): PriceList
    {
        $priceList = new PriceList($this->data);
        $priceList->save();

        // Create Discount
        if (($discount = ($this->data['discount'] ?? false)) && $this->data['discount']['discount'] != 0) {
            CreateDiscount::make(
                array_merge(
                    $discount,
                    [
                        'model_type' => PriceList::class,
                        'model_id' => $priceList->id,
                    ]
                )
            )->execute();
        }

        return $priceList->fresh($discount ? ['discount'] : []);
    }
}
