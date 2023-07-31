<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\FinishProjectRequest;
use FluxErp\Models\Project;
use FluxErp\States\Project\Done;
use Illuminate\Database\Eloquent\Model;

class FinishProject extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new FinishProjectRequest())->rules();
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): Model
    {
        $project = Project::query()
            ->whereKey($this->data['id'])
            ->first();

        $project->state = $this->data['finish'] ? Done::class : Project::getDefaultStateFor('state');
        $project->save();

        return $project;
    }
}
