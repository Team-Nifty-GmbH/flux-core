<?php

namespace FluxErp\Rulesets\Plugin;

use FluxErp\Rulesets\FluxRuleset;

class TogglePluginRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
        ];
    }
}
