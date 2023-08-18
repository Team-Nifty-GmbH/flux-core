<?php

namespace FluxErp\Http\Requests;

class CreateWorkTimeTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:work_time_types,uuid',
            'name' => 'required|string',
            'is_billable' => 'boolean',
        ];
    }
}
