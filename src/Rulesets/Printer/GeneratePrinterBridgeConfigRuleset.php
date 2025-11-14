<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Rulesets\FluxRuleset;

class GeneratePrinterBridgeConfigRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'instance_name' => 'nullable|string|max:255',
            'printer_check_interval' => 'nullable|integer|min:1',
            'job_check_interval' => 'nullable|integer|min:1',
            'api_token' => 'nullable|string',
            'api_port' => 'nullable|integer|min:1|max:65535',
            'reverb_disabled' => 'nullable|boolean',
        ];
    }
}