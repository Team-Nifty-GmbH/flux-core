<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\UniqueInFieldDependence;

class UpdateTranslationRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:language_lines,id',
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
