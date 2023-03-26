<?php

namespace FluxErp\Http\Requests;

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
            'id' => 'required|integer|exists:comments,id,deleted_at,NULL',
            'is_internal' => 'required_without:is_sticky|boolean',
            'is_sticky' => 'required_without:is_internal|boolean',
        ];
    }
}
