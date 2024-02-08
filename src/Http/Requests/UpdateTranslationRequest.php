<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;

class UpdateTranslationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(LanguageLine::class),
            ],
            'group' => 'sometimes|required|string',
            'key' => [
                'sometimes',
                'required',
                'string',
                new UniqueInFieldDependence(LanguageLine::class, 'group'),
            ],
            'text' => [
                'sometimes',
                'required',
                new ArrayIsKeyValuePair(),
            ],
        ];
    }
}
