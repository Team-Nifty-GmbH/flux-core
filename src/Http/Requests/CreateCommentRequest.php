<?php

namespace FluxErp\Http\Requests;

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
            'model_id' => 'required|integer',
            'model_type' => 'required|string',
            'parent_id' => 'integer|exists:comments,id|nullable',
            'comment' => 'required|string',
            'is_internal' => 'sometimes|required|boolean',
            'is_sticky' => 'sometimes|required|boolean',
        ];
    }
}
