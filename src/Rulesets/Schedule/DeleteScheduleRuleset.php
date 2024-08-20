<?php

namespace FluxErp\Rulesets\Schedule;

use FluxErp\Models\Schedule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteScheduleRuleset extends FluxRuleset
{
    protected static ?string $model = Schedule::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Schedule::class]),
            ],
        ];
    }
}
