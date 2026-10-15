<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\ProductConfiguration\ConfigurationRuleset;

class CalculateProductConfigurationPriceRuleset extends FluxRuleset
{
    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ConfigurationRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_configurable', true),
            ],
            'price_list_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'amount' => [
                'nullable',
                app(Numeric::class),
            ],
        ];
    }
}
