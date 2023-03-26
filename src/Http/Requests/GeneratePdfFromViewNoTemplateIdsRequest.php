<?php

namespace FluxErp\Http\Requests;

class GeneratePdfFromViewNoTemplateIdsRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'preview' => 'sometimes|boolean|declined',
            'store' => 'boolean|accepted',
            'store_pdf' => 'sometimes|boolean|declined',
            'is_template' => 'sometimes|boolean|declined',
        ];
    }
}
