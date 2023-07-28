<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProjectTask extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProjectTaskRequest())->rules();
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function performAction(): ProjectTask
    {
        $projectTask = new ProjectTask($this->data);
        $projectTask->save();

        return $projectTask;
    }

    public function validateData(): void
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
    }
}
