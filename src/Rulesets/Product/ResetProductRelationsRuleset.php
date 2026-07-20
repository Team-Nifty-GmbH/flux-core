<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ResetProductRelationsRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'parent_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->whereNull('parent_id'),
            ],
            'relations' => 'required|array|min:1',
            'relations.*.relation' => [
                'required',
                'string',
                Rule::in(app(Product::class)->getInheritableRelations()),
            ],
            'relations.*.related_id' => 'integer|nullable',
            'variant_ids' => 'array|nullable',
            'variant_ids.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->whereNotNull('parent_id'),
            ],
        ];
    }
}
