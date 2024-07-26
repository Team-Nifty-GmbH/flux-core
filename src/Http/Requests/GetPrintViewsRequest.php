<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Printable;

class GetPrintViewsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['uses' => Printable::class]),
            ],
            'model_id' => [
                'integer',
                app(MorphExists::class),
            ],
        ];
    }
}
