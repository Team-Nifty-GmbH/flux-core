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
            'printers' => 'nullable|array',
            'printers.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Printer::class]),
            ],
        ];
    }
}
