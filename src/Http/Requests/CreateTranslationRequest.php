<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\UniqueInFieldDependence;

class CreateTranslationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'group' => 'required|string',
            'key' => [
                'required',
                'string',
                new UniqueInFieldDependence(LanguageLine::class, 'group', false),
            ],
            'text' => [
                'required',
                new ArrayIsKeyValuePair(),
            ],
        ];
    }
}
