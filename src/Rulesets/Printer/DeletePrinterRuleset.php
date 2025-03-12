<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Models\Printer;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePrinterRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Printer::class]),
            ],
        ];
    }
}
