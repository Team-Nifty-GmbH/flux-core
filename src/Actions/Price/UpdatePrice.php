<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePriceRequest;
use FluxErp\Models\Price;
use Illuminate\Database\Eloquent\Model;

class UpdatePrice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePriceRequest())->rules();
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function performAction(): Model
    {
        $price = Price::query()
            ->whereKey($this->data['id'])
            ->first();

        $price->fill($this->data);
        $price->save();

        return $price->withoutRelations()->fresh();
    }
}
