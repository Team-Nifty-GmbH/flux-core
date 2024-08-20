<?php

namespace FluxErp\Rulesets\CommissionRate;

use FluxErp\Models\Category;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCommissionRateRuleset extends FluxRuleset
{
    protected static ?string $model = CommissionRate::class;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'category_id' => [
                'exclude_unless:product_id,null',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Category::class])
                    ->where('model_type', morph_alias(Product::class)),
            ],
            'product_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
