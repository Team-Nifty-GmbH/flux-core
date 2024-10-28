<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\CreateProjectRuleset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProject extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateProjectRuleset::class;
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): Project
    {
        $project = app(Project::class, ['attributes' => $this->data]);
        $project->save();

        return $project->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Project::class));

        $this->data = $validator->validate();

        if ($this->data['parent_id'] ?? false) {
            $parentProject = resolve_static(Project::class, 'query')
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
