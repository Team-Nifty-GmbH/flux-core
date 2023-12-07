<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Printable;
use Illuminate\Database\Eloquent\Model;

class PrintingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'string',
                'nullable',
                new ClassExists(uses: Printable::class, instanceOf: Model::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                new MorphExists(),
            ],
            'view' => 'required|string',
            'html' => 'exclude_if:preview,true|boolean',
            'preview' => 'exclude_if:html,true|boolean',
        ];
    }
}
