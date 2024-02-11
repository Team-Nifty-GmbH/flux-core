<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rules\ModelExists;

class UpdateFormBuilderFieldResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderFieldResponse::class),
            ],
            'response' => 'required|string',
        ];
    }
}
