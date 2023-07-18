<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\FinishProjectRequest;
use FluxErp\Models\Project;
use FluxErp\States\Project\Done;
use Illuminate\Database\Eloquent\Model;

class FinishProject extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new FinishProjectRequest())->rules();
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute(): Model
    {
        $project = Project::query()
            ->whereKey($this->data['id'])
            ->first();

        $project->state = $this->data['finish'] ? Done::class : Project::getDefaultStateFor('state');
        $project->save();

        return $project;
    }
}
