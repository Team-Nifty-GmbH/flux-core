<?php

namespace FluxErp\Rulesets\Comment;

use FluxErp\Models\Comment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Commentable;

class CreateCommentRuleset extends FluxRuleset
{
    protected static ?string $model = Comment::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:comments,uuid',
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(uses: Commentable::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Comment::class),
            ],
            'comment' => 'required|string',
            'is_internal' => 'sometimes|required|boolean',
            'is_sticky' => 'sometimes|required|boolean',
        ];
    }
}
