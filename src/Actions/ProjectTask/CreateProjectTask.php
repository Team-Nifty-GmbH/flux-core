<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProjectTask implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProjectTaskRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project-task.create';
    }

    public static function description(): string|null
    {
        return 'create project task';
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function execute(): ProjectTask
    {
        $projectTask = new ProjectTask($this->data);
        $projectTask->save();

        return $projectTask;
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

        if (($this->data['category_id'] ?? false)
            && ! Project::query()
                ->whereKey($this->data['project_id'])
                ->first()
                ->categories()
                ->whereKey($this->data['category_id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'category_id' => [__('Category not found in project')],
            ])->errorBag('createProjectTask');
        }

        return $this;
    }
}
