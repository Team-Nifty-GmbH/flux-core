<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\ProjectTask;
use Illuminate\Support\Facades\Validator;

class DeleteProjectTask implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:project_tasks,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'project-task.delete';
    }

    public static function description(): string|null
    {
        return 'delete project task';
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function execute(): bool|null
    {
        return ProjectTask::query()
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

        return $this;
    }
}
