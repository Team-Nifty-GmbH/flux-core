<?php

namespace FluxErp\Actions\Task;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Task;
use FluxErp\Rulesets\Task\DeleteTaskRuleset;

class DeleteTask extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteTaskRuleset::class;
    }

    public static function models(): array
    {
        return [Task::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Task::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
