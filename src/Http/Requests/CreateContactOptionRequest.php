<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Models\Setting;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class CreateContactOptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(
                    Setting::query()
                        ->where('key', 'contact-options.types')
                        ->first()
                        ?->toArray()['settings'] ?: ['phone', 'email', 'website']
                ),
            ],
            'label' => 'required|string',
            'value' => 'required|string',
        ];
    }
}
