<?php

namespace FluxErp\Rulesets\Record;

use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class MergeRecordsRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'main_record' => 'required|array',
            'main_record.id' => [
                'required',
                'integer',
                app(MorphExists::class, ['withPrefix' => false]),
            ],
            'main_record.columns' => 'nullable|array',
            'main_record.columns.*' => 'nullable|string',
            'merge_records' => 'required|array',
            'merge_records.*' => 'required|array',
            'merge_records.*.id' => [
                'required',
                'integer',
                app(MorphExists::class, ['withPrefix' => false]),
            ],
            'merge_records.*.columns' => 'nullable|array',
            'merge_records.*.columns.*' => 'nullable|string',
        ];
    }
}
