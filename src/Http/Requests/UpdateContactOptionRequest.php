<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Setting;
use Illuminate\Validation\Rule;

class UpdateContactOptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:contact_options,id',
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
