<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\DiscountGroup\UpdateDiscountGroupRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateDiscountGroup extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateDiscountGroupRuleset::class;
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): Model
    {
        $discounts = Arr::pull($this->data, 'discounts');

        $discountGroup = resolve_static(DiscountGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $discountGroup->fill($this->data);
        $discountGroup->save();

        if (! is_null($discounts)) {
            $discountGroup->discounts()->sync($discounts);
        }

        return $discountGroup->withoutRelations()->fresh();
    }
}
