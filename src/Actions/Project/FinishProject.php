<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\FinishProjectRuleset;
use FluxErp\States\Project\Done;
use Illuminate\Database\Eloquent\Model;

class FinishProject extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(FinishProjectRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): Model
    {
        $project = app(Project::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $project->state = $this->data['finish'] ?
            Done::class :
            resolve_static(Project::class, 'getDefaultStateFor', ['state']);
        $project->save();

        return $project->fresh();
    }
}
