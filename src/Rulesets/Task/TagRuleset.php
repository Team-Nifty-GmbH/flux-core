<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Tag;
use FluxErp\Models\Task;
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
                app(ModelExists::class, ['model' => Tag::class])
                    ->where('type', morph_alias(Task::class)),
            ],
        ];
    }
}
