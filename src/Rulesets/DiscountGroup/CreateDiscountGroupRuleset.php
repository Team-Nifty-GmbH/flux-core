<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\FluxRuleset;

class CreateDiscountGroupRuleset extends FluxRuleset
{
    protected static ?string $model = DiscountGroup::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:discount_groups,uuid',
            'name' => 'required|string',
            'is_active' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules')
        );
    }
}
