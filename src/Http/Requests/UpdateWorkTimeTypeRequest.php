<?php

namespace FluxErp\Http\Requests;

class UpdateWorkTimeTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:work_time_types,id,deleted_at,NULL',
            'name' => 'sometimes|required|string',
            'is_billable' => 'boolean',
        ];
    }
}
