<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Printer;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class PrinterRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'printers' => 'array',
            'printers.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Printer::class]),
            ],
        ];
    }
}
