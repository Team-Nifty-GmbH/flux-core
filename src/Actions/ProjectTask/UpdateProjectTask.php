<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProjectTask extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProjectTaskRequest())->rules();
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function performAction(): Model
    {
        $task = ProjectTask::query()
            ->whereKey($this->data['id'])
            ->first();

        $task->fill($this->data);
        $task->save();

        return $task->withoutRelations()->fresh();
    }

    public function validateData(): void
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
                'category_id' => [__('Project category not found')],
            ])->errorBag('updateProjectTask');
        }
    }
}
