<?php

namespace FluxErp\Http\Requests;

use FluxErp\Contracts\OffersPrinting;
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
                'required',
                'string',
                new ClassExists(uses: Printable::class, instanceOf: Model::class, implements: OffersPrinting::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'view' => 'required|string',
            'html' => 'exclude_if:preview,true|boolean',
            'preview' => 'boolean',
        ];
    }
}
