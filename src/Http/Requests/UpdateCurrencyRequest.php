<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Currency;
use FluxErp\Rules\ModelExists;

class UpdateCurrencyRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Currency::class),
            ],
            'name' => 'string',
            'iso' => 'string|unique:currencies,iso',
            'symbol' => 'string',
            'is_default' => 'boolean',
        ];
    }
}
