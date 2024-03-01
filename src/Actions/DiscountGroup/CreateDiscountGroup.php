<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\DiscountGroup\CreateDiscountGroupRuleset;
use Illuminate\Support\Arr;

class CreateDiscountGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateDiscountGroupRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): DiscountGroup
    {
        $discounts = Arr::pull($this->data, 'discounts', []);

        $discountGroup = app(DiscountGroup::class, ['attributes' => $this->data]);
        $discountGroup->save();

        if ($discounts) {
            $discountGroup->discounts()->attach($discounts);
        }

        return $discountGroup->withoutRelations()->fresh();
    }
}
