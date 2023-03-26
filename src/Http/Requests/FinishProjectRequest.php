<?php

namespace FluxErp\Http\Requests;

class FinishProjectRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
            'finish' => 'required|boolean',
        ];
    }
}
