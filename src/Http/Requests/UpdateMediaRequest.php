<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateMediaRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:media,id',
            'parent_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('media', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string',
            'collection' => 'sometimes|required|string',
            'categories' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
