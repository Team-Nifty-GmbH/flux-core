<?php

namespace FluxErp\Rulesets\Comment;

use FluxErp\Models\Comment;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
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
                app(MorphClassExists::class, ['uses' => Commentable::class]),
            ],
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(MorphExists::class, ['model' => Comment::class]),
            ],
            'comment' => 'required|string',
            'is_internal' => 'sometimes|required|boolean',
            'is_sticky' => 'sometimes|required|boolean',
        ];
    }
}
