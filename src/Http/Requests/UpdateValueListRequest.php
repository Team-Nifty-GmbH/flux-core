<?php

namespace FluxErp\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateValueListRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('additional_columns', 'id')->whereNotNull('values'),
            ],
            'name' => 'sometimes|required|string',
            'values' => 'sometimes|required|array',
        ];
    }
}
