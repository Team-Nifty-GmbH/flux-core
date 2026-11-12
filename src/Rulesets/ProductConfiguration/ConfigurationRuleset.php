<?php

namespace FluxErp\Rulesets\ProductConfiguration;

use FluxErp\Rulesets\FluxRuleset;

class ConfigurationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'configuration' => 'nullable|array',
            'configuration.*.product_configuration_group_item_id' => 'required|integer',
            'configuration.*.amount' => 'required|integer|min:1',
        ];
    }
}
