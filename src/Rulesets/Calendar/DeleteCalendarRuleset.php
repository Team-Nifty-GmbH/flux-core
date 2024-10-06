<?php

namespace FluxErp\Rulesets\Calendar;

use FluxErp\Models\Calendar;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCalendarRuleset extends FluxRuleset
{
    protected static ?string $model = Calendar::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (app(ModelExists::class, ['model' => Calendar::class]))->whereDoesntHave('children'),
            ],
        ];
    }
}
