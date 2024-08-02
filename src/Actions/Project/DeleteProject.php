<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Project;
use FluxErp\Rulesets\Project\DeleteProjectRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProject extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteProjectRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Project::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Project::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->children()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'children' => [__('The given project has children')],
            ])->errorBag('deleteProject');
        }
    }
}
