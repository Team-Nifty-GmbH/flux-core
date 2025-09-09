<?php

namespace FluxErp\Rulesets\PrintLayoutSnippet;

use FluxErp\Models\PrintLayout;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePrintLayoutSnippetRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'print_layout_id'   => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintLayout::class]),
            ],
            'content' => 'required|string',
        ];
    }
}
