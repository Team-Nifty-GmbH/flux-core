<?php

namespace FluxErp\Actions\Project;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteProject implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project.delete';
    }

    public static function description(): string|null
    {
        return 'delete project';
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute()
    {
        return Project::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

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
