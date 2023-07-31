<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\FinishProjectTaskRequest;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;

class FinishProjectTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new FinishProjectTaskRequest())->rules();
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

        $task->is_done = $this->data['finish'];
        $task->save();

        return $task;
    }
}
