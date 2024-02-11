<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Comment;
use FluxErp\Rules\ModelExists;

class UpdateCommentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Comment::class),
            ],
            'is_internal' => 'required_without:is_sticky|boolean',
            'is_sticky' => 'required_without:is_internal|boolean',
        ];
    }
}
