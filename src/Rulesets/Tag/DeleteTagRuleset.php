<?php

namespace FluxErp\Rulesets\Tag;

use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTagRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Tag::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tag::class]),
            ],
        ];
    }
}
