<?php

namespace FluxErp\Rulesets\PrintLayout;

use FluxErp\Models\PrintLayout;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePrintLayoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintLayout::class]),
            ],
        ];
    }
}
