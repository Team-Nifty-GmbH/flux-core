<?php

namespace FluxErp\Rulesets\Lead;

use FluxErp\Models\Category;
use FluxErp\Models\Lead;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CategoryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'categories' => 'array|nullable',
            'categories.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Category::class])
                    ->where('model_type', morph_alias(Lead::class)),
            ],
        ];
    }
}
