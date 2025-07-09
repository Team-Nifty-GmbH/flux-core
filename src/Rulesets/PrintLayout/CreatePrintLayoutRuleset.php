<?php

namespace FluxErp\Rulesets\PrintLayout;

use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePrintLayoutRuleset extends FluxRuleset
{

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:print_layouts,name',
            ],
            'model_type' => [
                'required',
                'string',
                'max:255',
                app(MorphClassExists::class),
            ],
            'margin' => 'nullable|array',
            'header' => 'nullable|array',
            'footer' => 'nullable|array',
            'first_page_header' => 'nullable|array',
        ];
    }
}
