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
                new ModelExists(User::class),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'category_id' => [
                'exclude_unless:product_id,null',
                'integer',
                'nullable',
                (new ModelExists(Category::class))
                    ->where('model_type', app(Product::class)->getMorphClass()),
            ],
            'product_id' => [
                'integer',
                'nullable',
                new ModelExists(Product::class),
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
