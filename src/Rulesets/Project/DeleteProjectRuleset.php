<?php

namespace FluxErp\Rulesets\Project;

use FluxErp\Models\Project;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProjectRuleset extends FluxRuleset
{
    protected static ?string $model = Project::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Project::class),
            ],
        ];
    }
}
