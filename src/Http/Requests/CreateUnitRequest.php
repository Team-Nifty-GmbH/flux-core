<?php

namespace FluxErp\Http\Requests;

class CreateUnitRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:units,uuid',
            'name' => 'required|string',
            'abbreviation' => 'required|string',
        ];
    }
}
