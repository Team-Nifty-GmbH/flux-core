<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class TagRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'tags' => 'array',
            'tags.*' => [
                'required',
                'integer',
                (new ModelExists(Tag::class))->where('type', app(OrderPosition::class)->getMorphClass()),
            ],
        ];
    }
}
