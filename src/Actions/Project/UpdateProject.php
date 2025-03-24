<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\UpdateProjectRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateProject extends FluxAction
{
    public static function models(): array
    {
        return [Project::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProjectRuleset::class;
    }

    public function performAction(): Model
    {
        $project = resolve_static(Project::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $project->fill($this->data);
        $project->save();

        return $project->withoutRelations()->fresh();
    }
}
