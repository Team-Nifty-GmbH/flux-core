<?php

namespace FluxErp\Rulesets\Tag;

use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTagRuleset extends FluxRuleset
{
    protected static ?string $model = Tag::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Tag::class),
            ],
        ];
    }
}
