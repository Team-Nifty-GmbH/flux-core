<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rules\ModelExists;

class CreateFormBuilderFieldResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderForm::class),
            ],
            'field_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderField::class),
            ],
            'response_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderResponse::class),
            ],
            'response' => 'required|string',
        ];
    }
}
