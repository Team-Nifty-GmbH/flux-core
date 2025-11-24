<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Rulesets\FluxRuleset;

class GeneratePrinterBridgeConfigRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'instance_name' => 'required|string|max:255',
            'printer_check_interval' => 'required|integer|min:1',
            'job_check_interval' => 'required|integer|min:1',
            'api_port' => 'required|integer|min:1|max:65535',
            'reverb_disabled' => 'required|boolean',
            'force_regenerate' => 'required|boolean',
        ];
    }
}
