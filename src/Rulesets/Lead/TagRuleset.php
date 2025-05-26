<?php

namespace FluxErp\Rulesets\Lead;

use FluxErp\Models\Lead;
use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class TagRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'tags' => 'nullable|array',
            'tags.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tag::class])
                    ->where('type', morph_alias(Lead::class)),
            ],
        ];
    }
}
