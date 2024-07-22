<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\FinishTaskRuleset;
use Illuminate\Database\Eloquent\Model;

class FinishTask extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(FinishTaskRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Task::class];
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
