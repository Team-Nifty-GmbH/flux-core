<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Models\PriceList;

class CreatePriceList extends BaseAction
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

        return $priceList;
    }
}
