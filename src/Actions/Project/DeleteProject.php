<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Project;
use Illuminate\Validation\ValidationException;

class DeleteProject extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function performAction(): ?bool
    {
        return Project::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Project::query()
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
