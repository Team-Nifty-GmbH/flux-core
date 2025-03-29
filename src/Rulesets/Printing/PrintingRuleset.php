<?php

namespace FluxErp\Rulesets\Printing;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Printable;

class PrintingRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                'max:255',
                app(MorphClassExists::class, ['uses' => Printable::class, 'implements' => OffersPrinting::class]),
            ],
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'view' => 'required|string',
            'html' => 'exclude_if:preview,true|boolean',
            'preview' => 'boolean',
        ];
    }
}
