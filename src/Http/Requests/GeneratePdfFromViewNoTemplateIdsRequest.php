<?php

namespace FluxErp\Http\Requests;

class GeneratePdfFromViewNoTemplateIdsRequest extends BaseFormRequest
{
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
