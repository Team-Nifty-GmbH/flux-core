<?php

namespace FluxErp\Rulesets\Comment;

use FluxErp\Models\Comment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCommentRuleset extends FluxRuleset
{
    protected static ?string $model = Comment::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Comment::class]),
            ],
        ];
    }
}
