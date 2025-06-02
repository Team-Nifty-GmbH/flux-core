<?php

namespace FluxErp\Rulesets\Console;

use FluxErp\Rules\ClassExists;
use FluxErp\Rulesets\FluxRuleset;

class RunCommandRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'command' => [
                'required',
                'string',
                app(ClassExists::class),
            ],
            'arguments' => 'array',
            'arguments.*' => 'string',
        ];
    }
}
