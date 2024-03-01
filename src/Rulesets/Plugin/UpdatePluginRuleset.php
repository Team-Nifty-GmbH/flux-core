<?php

namespace FluxErp\Rulesets\Plugin;

use FluxErp\Rulesets\FluxRuleset;

class UpdatePluginRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'packages' => 'present|array',
            'packages.*' => 'required|string',
            'migrate' => 'boolean',
        ];
    }
}
