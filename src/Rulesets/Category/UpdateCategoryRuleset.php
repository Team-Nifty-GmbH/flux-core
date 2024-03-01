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
                new ModelExists(Category::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Category::class),
            ],
            'name' => 'required|string',
            'sort_number' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
