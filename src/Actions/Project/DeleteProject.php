<?php

namespace FluxErp\Actions\Project;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Project;
use Illuminate\Validation\ValidationException;

class DeleteProject extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute(): bool|null
    {
        return Project::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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

        return $this;
    }
}
