<?php

namespace FluxErp\Rulesets\Project;

use FluxErp\Models\Project;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class FinishProjectRuleset extends FluxRuleset
{
    protected static ?string $model = Project::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Project::class]),
            ],
            'finish' => 'required|boolean',
        ];
    }
}
