<?php

namespace FluxErp\Rulesets\Category;

use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCategoryRuleset extends FluxRuleset
{
    protected static ?string $model = Category::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Category::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Category::class]),
            ],
            'name' => 'required|string',
            'sort_number' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
