<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateTaskRequest;
use FluxErp\Models\Task;
use Illuminate\Support\Facades\Validator;

class CreateTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTaskRequest())->rules();
    }

    public static function models(): array
    {
        return [Task::class];
    }

    public function performAction(): Task
    {
        $task = new Task($this->data);
        $task->save();

        return $task->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Task());

        $this->data = $validator->validate();
    }
}
