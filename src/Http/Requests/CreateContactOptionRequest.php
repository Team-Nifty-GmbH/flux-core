<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Setting;
use Illuminate\Validation\Rule;

class CreateContactOptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address_id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
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
