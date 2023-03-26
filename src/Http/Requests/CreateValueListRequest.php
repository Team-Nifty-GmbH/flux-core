<?php

namespace FluxErp\Http\Requests;

class CreateValueListRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'model_type' => 'required|string',
            'values' => 'required|array',
        ];
    }
}
