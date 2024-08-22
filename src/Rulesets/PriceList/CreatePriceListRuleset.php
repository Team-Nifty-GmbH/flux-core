<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePriceListRuleset extends FluxRuleset
{
    protected static ?string $model = PriceList::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:price_lists,uuid',
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'name' => 'required|string',
            'price_list_code' => 'required|string|unique:price_lists,price_list_code',
            'is_net' => 'required|boolean',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountRuleset::class, 'getRules'),
            resolve_static(RoundingRuleset::class, 'getRules')
        );
    }
}
