<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ResetProductRelationRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
                Rule::exists('products', 'id')->whereNotNull('parent_id'),
            ],
            'relation' => [
                'required',
                'string',
                Rule::in(app(Product::class)->getInheritableRelations()),
            ],
            'key' => ['nullable'],
        ];
    }
}
