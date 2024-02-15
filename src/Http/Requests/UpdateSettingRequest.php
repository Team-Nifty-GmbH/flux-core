<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Setting;
use FluxErp\Rules\ModelExists;

class UpdateSettingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Setting::class),
            ],
            'settings' => 'required|array',
        ];
    }
}
