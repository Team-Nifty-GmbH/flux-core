<?php

namespace FluxErp\Rulesets\Tag;

use FluxErp\Models\Tag;
use FluxErp\Rulesets\FluxRuleset;

class CreateTagRuleset extends FluxRuleset
{
    protected static ?string $model = Tag::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],
            'type' => 'string|max:255',
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer|min:0',
        ];
    }
}
