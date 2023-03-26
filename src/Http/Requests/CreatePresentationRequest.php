<?php

namespace FluxErp\Http\Requests;

class CreatePresentationRequest extends BaseFormRequest
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
            'notice' => 'sometimes|string|nullable',
            'model_id' => 'integer|required_with:model_type',
            'model_type' => 'string|required_with:model_id',
            'is_public' => 'boolean',
        ];
    }
}
