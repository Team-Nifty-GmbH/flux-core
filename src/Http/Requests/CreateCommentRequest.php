<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Comment;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Commentable;
use Illuminate\Database\Eloquent\Model;

class CreateCommentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:comments,uuid',
            'model_type' => [
                'required',
                'string',
                new ClassExists(uses: Commentable::class, instanceOf: Model::class),
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
