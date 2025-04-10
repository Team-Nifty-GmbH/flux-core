<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\FinishProjectRuleset;
use FluxErp\States\Project\Done;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class FinishProject extends FluxAction
{
    public static function models(): array
    {
        return [Project::class];
    }

    protected static function getSuccessCode(): ?int
    {
        return Response::HTTP_OK;
    }

    protected function getRulesets(): string|array
    {
        return FinishProjectRuleset::class;
    }

    public function performAction(): Model
    {
        $project = resolve_static(Project::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $project->state = $this->data['finish'] ?
            Done::class :
            resolve_static(Project::class, 'getDefaultStateFor', ['state']);
        $project->save();

        return $project->fresh();
    }
}
