<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\RouteExists;

class LoginUrlRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'redirect' => [
                'nullable',
                'string',
                app(RouteExists::class, ['parameterAttribute' => 'redirect_params']),
            ],
            'redirect_params' => [
                'nullable',
                'array',
            ],
        ];
    }
}
