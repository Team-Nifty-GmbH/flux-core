<?php

namespace FluxErp\Rulesets\Product\Variant;

use FluxErp\Models\Client;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\Product\CreateProductRuleset;
use FluxErp\Rulesets\Product\ProductOptionRuleset;
use Illuminate\Support\Arr;

class CreateVariantsRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'parent_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'name' => 'string',
            'clients' => 'array',
            'clients.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'product_options' => 'required|array',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            Arr::except(
                resolve_static(CreateProductRuleset::class, 'getRules'),
                [
                    'uuid',
                    'parent_id',
                    'cover_media_id',
                    'product_number',
                    'product_options',
                    'ean',
                    'is_bundle',

                    'product_options',
                    'clients',
                ]
            ),
            Arr::mapWithKeys(
                resolve_static(ProductOptionRuleset::class, 'getRules'),
                fn ($item, $key) => [$key.'.*' => $item]
            ),
            parent::getRules(),
        );
    }
}
