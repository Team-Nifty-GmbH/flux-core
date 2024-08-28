<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CategoryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'categories' => 'array',
            'categories.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Category::class])
                    ->where('model_type', morph_alias(Contact::class)),
            ],
        ];
    }
}
