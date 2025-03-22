<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\CreateProjectRuleset;
use Illuminate\Validation\ValidationException;

class CreateProject extends FluxAction
{
    public static function models(): array
    {
        return [Project::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProjectRuleset::class;
    }

    public function performAction(): Project
    {
        $project = app(Project::class, ['attributes' => $this->data]);
        $project->save();

        return $project->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

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
