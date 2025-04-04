<?php

namespace FluxErp\Rulesets\DiscountGroup;

use FluxErp\Models\DiscountGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateDiscountGroupRuleset extends FluxRuleset
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
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DiscountGroup::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
