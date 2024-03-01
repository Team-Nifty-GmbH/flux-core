<?php

namespace FluxErp\Rulesets\Tag;

use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTagRuleset extends FluxRuleset
{
    protected static ?string $model = Tag::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Tag::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer|min:0',
        ];
    }
}
