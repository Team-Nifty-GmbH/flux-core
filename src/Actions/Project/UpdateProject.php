<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateProjectRequest;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Model;

class UpdateProject extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProjectRequest())->rules();
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

        $project->fill($this->data);
        $project->save();

        return $project->withoutRelations()->fresh();
    }
}
