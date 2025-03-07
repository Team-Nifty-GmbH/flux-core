<?php

namespace FluxErp\Rulesets\PrintJob;

use FluxErp\Models\PrintJob;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePrintJobRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintJob::class])
                    ->where('is_completed', false),
            ],
        ];
    }
}
