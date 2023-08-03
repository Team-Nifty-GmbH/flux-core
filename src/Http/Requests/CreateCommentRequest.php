<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Commentable;
use Illuminate\Database\Eloquent\Model;

class CreateCommentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'parent_id' => 'integer|exists:comments,id|nullable',
            'comment' => 'required|string',
            'is_internal' => 'sometimes|required|boolean',
            'is_sticky' => 'sometimes|required|boolean',
        ];
    }
}
