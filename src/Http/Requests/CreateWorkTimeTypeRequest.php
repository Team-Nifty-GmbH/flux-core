<?php

namespace FluxErp\Http\Requests;

class CreateWorkTimeTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:work_time_types,uuid',
            'name' => 'required|string',
            'is_billable' => 'boolean',
        ];
    }
}
