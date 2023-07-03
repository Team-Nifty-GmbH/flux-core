<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Presentation;

class CreatePresentationRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Presentation())->hasAdditionalColumnsValidationRules(),
            [
                'name' => 'required|string',
                'notice' => 'sometimes|string|nullable',
                'model_id' => 'integer|required_with:model_type',
                'model_type' => 'string|required_with:model_id',
                'is_public' => 'boolean',
            ],
        );
    }
}
