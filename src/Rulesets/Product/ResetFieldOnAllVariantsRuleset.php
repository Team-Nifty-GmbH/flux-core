<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ResetFieldOnAllVariantsRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'parent_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
                Rule::exists('products', 'id')->whereNull('parent_id'),
            ],
            'field' => [
                'required',
                'string',
                Rule::in(app(Product::class)->getInheritableFields()),
            ],
        ];
    }
}
