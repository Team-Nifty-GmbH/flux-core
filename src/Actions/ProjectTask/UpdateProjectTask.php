<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProjectTask implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateProjectTaskRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project-task.update';
    }

    public static function description(): string|null
    {
        return 'update project task';
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function execute(): Model
    {
        $task = ProjectTask::query()
            ->whereKey($this->data['id'])
            ->first();

        $task->fill($this->data);
        $task->save();

        return $task->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProjectTask());

        $this->data = $validator->validate();

        $project = ($this->data['project_id'] ?? false)
            ? Project::query()->whereKey($this->data['project_id'])->first()
            : ProjectTask::query()
                ->whereKey($this->data['id'])
                ->first()
                ->project;

        if (array_key_exists('category_id', $this->data)
            && ! $project->categories()->whereKey($this->data['category_id'])->exists()
        ) {
            throw ValidationException::withMessages([
                'category_id' => [__('Project category not found')]
            ])->errorBag('updateProjectTask');
        }

        return $this;
    }
}
