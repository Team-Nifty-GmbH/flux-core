<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Task;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class FinishTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Task::class]),
            ],
            'finish' => 'required|boolean',
        ];
    }
}
