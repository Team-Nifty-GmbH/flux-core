<?php

namespace FluxErp\Rulesets\PrintLayout;

use FluxErp\Models\PrintLayout;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePrintLayoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintLayout::class]),
            ],
            'margin' => 'nullable|array',
            'header' => 'nullable|array',
            'footer' => 'nullable|array',
            'first_page_header' => 'nullable|array',
            'temporaryMedia' => 'array',
        ];
    }

}
