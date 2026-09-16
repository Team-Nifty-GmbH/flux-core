<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ResetProductFieldsRuleset extends FluxRuleset
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
            'fields' => 'required|array|min:1',
            'fields.*' => [
                'required',
                'string',
                Rule::in(app(Product::class)->getInheritableFields()),
            ],
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
