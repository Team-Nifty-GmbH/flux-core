<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\FinishTaskRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class FinishTask extends FluxAction
{
    public static ?int $successCode = Response::HTTP_OK;

    public static function models(): array
    {
        return [Task::class];
    }

    protected function getRulesets(): string|array
    {
        return FinishTaskRuleset::class;
    }

    public function performAction(): Model
    {
        $task = resolve_static(Task::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $task->is_done = $this->data['finish'];
        $task->save();

        return $task->fresh();
    }
}
