<?php

namespace FluxErp\Rulesets\Plugin;

use FluxErp\Rulesets\FluxRuleset;

class InstallPluginRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
            'options' => 'array',
            'options.*' => 'string',
            'migrate' => 'boolean',
        ];
    }
}
