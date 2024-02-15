<?php

namespace FluxErp\Http\Requests;

class CreateUnitRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:units,uuid',
            'name' => 'required|string',
            'abbreviation' => 'required|string',
        ];
    }
}
