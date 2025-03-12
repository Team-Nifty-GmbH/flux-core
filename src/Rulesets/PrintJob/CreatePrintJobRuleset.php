<?php

namespace FluxErp\Rulesets\PrintJob;

use FluxErp\Models\Media;
use FluxErp\Models\Printer;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePrintJobRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'media_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'printer_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Printer::class])
                    ->where('is_active', true),
            ],
            'user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'size' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
