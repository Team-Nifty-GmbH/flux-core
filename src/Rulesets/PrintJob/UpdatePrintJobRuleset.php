<?php

namespace FluxErp\Rulesets\PrintJob;

use FluxErp\Enums\PrintJobStatusEnum;
use FluxErp\Models\PrintJob;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePrintJobRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrintJob::class]),
            ],
            'quantity' => [
                'integer',
                'min:1',
            ],
            'size' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'is_completed' => [
                'boolean',
            ],
            'cups_job_id' => [
                'integer',
            ],
            'status' => [
                app(EnumRule::class, ['type' => PrintJobStatusEnum::class]),
            ],
            'error_message' => [
                'nullable',
                'string',
            ],
            'printed_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
