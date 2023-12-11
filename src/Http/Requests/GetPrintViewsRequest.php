<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Printable;
use Illuminate\Database\Eloquent\Model;

class GetPrintViewsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                new ClassExists(uses: Printable::class, instanceOf: Model::class),
            ],
            'model_id' => [
                'integer',
                new MorphExists(),
            ],
        ];
    }
}
