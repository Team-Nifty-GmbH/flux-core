<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Task;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Task::class),
            ],
        ];
    }
}
