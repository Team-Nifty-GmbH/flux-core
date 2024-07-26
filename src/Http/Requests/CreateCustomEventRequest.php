<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;

/**
 * @deprecated
 */
class CreateCustomEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|alpha|unique:custom_events,name',
            'model_type' => [
                'string',
                'nullable',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
        ];
    }
}
