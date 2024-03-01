<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\DiscountGroup\UpdateDiscountGroupRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateDiscountGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateDiscountGroupRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): Model
    {
        $discounts = Arr::pull($this->data, 'discounts');

        $discountGroup = app(DiscountGroup::class)->query()
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
