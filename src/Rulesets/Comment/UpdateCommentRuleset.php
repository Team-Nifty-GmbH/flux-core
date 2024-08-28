<?php

namespace FluxErp\Rulesets\Comment;

use FluxErp\Models\Comment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCommentRuleset extends FluxRuleset
{
    protected static ?string $model = Comment::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Comment::class]),
            ],
            'is_internal' => 'required_without:is_sticky|boolean',
            'is_sticky' => 'required_without:is_internal|boolean',
        ];
    }
}
