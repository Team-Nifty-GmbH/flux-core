<?php

namespace FluxErp\Http\Requests;

class FinishTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:tasks,id,deleted_at,NULL',
            'finish' => 'required|boolean',
        ];
    }
}
