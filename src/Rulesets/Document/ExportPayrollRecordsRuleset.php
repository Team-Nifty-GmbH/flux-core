<?php

namespace FluxErp\Rulesets\Document;

use FluxErp\Models\Media;
use FluxErp\Rulesets\FluxRuleset;

class ExportPayrollRecordsRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ];
    }
}
