<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\FinishTaskRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class FinishTask extends FluxAction
{
    public static function models(): array
    {
        return [Task::class];
    }

    protected static function getSuccessCode(): ?int
    {
        return parent::getSuccessCode() ?? Response::HTTP_OK;
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
