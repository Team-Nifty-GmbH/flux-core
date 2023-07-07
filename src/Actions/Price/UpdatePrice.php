<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdatePriceRequest;
use FluxErp\Models\Price;
use Illuminate\Database\Eloquent\Model;

class UpdatePrice extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdatePriceRequest())->rules();
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): Model
    {
        $price = Price::query()
            ->whereKey($this->data['id'])
            ->first();

        $price->fill($this->data);
        $price->save();

        return $price->withoutRelations()->fresh();
    }
}
