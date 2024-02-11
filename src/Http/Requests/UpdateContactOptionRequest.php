<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ContactOption;
use FluxErp\Models\Setting;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class UpdateContactOptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ContactOption::class),
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(
                    Setting::query()
                        ->where('key', 'contact-options.types')
                        ->first()
                        ?->toArray()['settings'] ?: ['phone', 'email', 'website']
                ),
            ],
            'label' => 'sometimes|required|string',
            'value' => 'sometimes|required|string',
        ];
    }
}
