<?php

namespace FluxErp\Rulesets\Printer;

use FluxErp\Rulesets\FluxRuleset;

class CreatePrinterRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'spooler_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'make_and_model' => 'nullable|string|max:255',
            'media_sizes' => 'required|array',
            'is_active' => 'boolean',
        ];
    }
}
