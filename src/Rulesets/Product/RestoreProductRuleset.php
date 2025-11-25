<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Media;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class RestoreProductRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->onlyTrashed(),
            ],
            'cover_media_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'vat_rate_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'purchase_unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'reference_unit_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'product_number' => 'string|nullable',
            'name' => 'string|max:255',
            'description' => 'string|nullable',
            'is_active' => 'boolean',
        ];
    }
}
