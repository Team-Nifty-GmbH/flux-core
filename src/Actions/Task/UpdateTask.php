<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTaskRequest;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTaskRequest())->rules();
    }

    public static function models(): array
    {
        return [Task::class];
    }

    public function performAction(): Model
    {
        $task = Task::query()
            ->whereKey($this->data['id'])
            ->first();

        $task->fill($this->data);
        $task->save();

        return $task->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Task());

        $this->data = $validator->validate();
    }
}
