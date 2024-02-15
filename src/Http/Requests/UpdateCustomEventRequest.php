<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\CustomEvent;
use FluxErp\Rules\ModelExists;

/**
 * @deprecated
 */
class UpdateCustomEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CustomEvent::class),
            ],
            'name' => 'required|alpha|unique:custom_events,name',
        ];
    }
}
