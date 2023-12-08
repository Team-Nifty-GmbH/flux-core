<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateProjectRequest;
use FluxErp\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProject extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProjectRequest())->rules();
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): Project
    {
        $project = new Project($this->data);
        $project->save();

        return $project->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Project());

        $this->data = $validator->validate();

        if ($this->data['parent_id'] ?? false) {
            $parentProject = Project::query()
                ->whereKey($this->data['parent_id'])
                ->first();

            if (! $parentProject) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Parent project not found')],
                ])->errorBag('createProject');
            }
        }
    }
}
