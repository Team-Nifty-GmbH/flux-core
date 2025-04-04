<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePriceListRuleset extends FluxRuleset
{
    protected static ?string $model = PriceList::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules'),
            resolve_static(RoundingRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'price_list_code' => 'sometimes|required|string|max:255',
            'is_net' => 'sometimes|boolean',
            'is_default' => 'boolean',
        ];
    }
}
