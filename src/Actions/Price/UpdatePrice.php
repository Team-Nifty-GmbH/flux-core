<?php

namespace FluxErp\Actions\Price;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Price;
use FluxErp\Rulesets\Price\UpdatePriceRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdatePrice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePriceRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function performAction(): Model
    {
        $price = app(Price::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $price->fill($this->data);
        $price->save();

        return $price->withoutRelations()->fresh();
    }
}
