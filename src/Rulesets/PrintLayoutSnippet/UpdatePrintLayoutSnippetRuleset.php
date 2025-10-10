<?php

namespace FluxErp\Rulesets\PrintLayoutSnippet;

use FluxErp\Models\PrintLayoutSnippet;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePrintLayoutSnippetRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintLayoutSnippet::class]),
            ],
            'content' => 'string',
        ];
    }
}
