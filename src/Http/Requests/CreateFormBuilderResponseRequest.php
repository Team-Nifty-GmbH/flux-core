<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class CreateFormBuilderResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderForm::class),
            ],
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
