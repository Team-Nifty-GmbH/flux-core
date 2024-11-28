<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Category;
use FluxErp\Models\Task;
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
                    ->where('model_type', morph_alias(Task::class)),
            ],
        ];
    }
}
