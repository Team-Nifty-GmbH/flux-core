<?php

namespace FluxErp\Rulesets\RecordOrigin;

use FluxErp\Models\RecordOrigin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteRecordOriginRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = RecordOrigin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => RecordOrigin::class]),
            ],
        ];
    }
}
