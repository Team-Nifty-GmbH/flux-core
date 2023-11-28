<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\FinishTaskRequest;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Model;

class FinishTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new FinishTaskRequest())->rules();
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

        $task->is_done = $this->data['finish'];
        $task->save();

        return $task->fresh();
    }
}
