<?php

namespace FluxErp\Actions\PriceList;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Models\PriceList;

class CreatePriceList extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreatePriceListRequest())->rules();
    }

    public static function models(): array
    {
        return [PriceList::class];
    }

    public function execute(): PriceList
    {
        $priceList = new PriceList($this->data);
        $priceList->save();

        return $priceList->fresh();
    }
}
