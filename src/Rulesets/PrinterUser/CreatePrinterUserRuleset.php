<?php

namespace FluxErp\Rulesets\PrinterUser;

use FluxErp\Models\Printer;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePrinterUserRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'printer_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Printer::class]),
            ],
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'default_size' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
