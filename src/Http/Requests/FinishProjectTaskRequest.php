<?php

namespace FluxErp\Http\Requests;

class FinishProjectTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:project_tasks,id,deleted_at,NULL',
            'finish' => 'required|boolean',
        ];
    }
}
