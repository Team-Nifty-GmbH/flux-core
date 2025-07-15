<?php

namespace FluxErp\Rulesets\PrintLayout;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePrintLayoutRuleset extends FluxRuleset
{

    // TODO: load all file names from printing directory

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
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
