<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\FluxRuleset;

class CreateDiscountGroupRuleset extends FluxRuleset
{
    protected static ?string $model = DiscountGroup::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:discount_groups,uuid',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
