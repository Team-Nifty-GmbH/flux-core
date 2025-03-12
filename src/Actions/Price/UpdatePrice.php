<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\UpdatePriceRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePrice extends FluxAction
{
    public static function models(): array
    {
        return [Price::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePriceRuleset::class;
    }

    public function performAction(): Model
    {
        $price = resolve_static(Price::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $price->fill($this->data);
        $price->save();

        return $price->withoutRelations()->fresh();
    }
}
